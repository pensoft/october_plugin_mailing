<?php namespace Pensoft\Mailing\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Input as FormInput;
use Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use October\Rain\Support\Facades\Flash;
use Pensoft\Mailing\Models\Groups;
use Pensoft\Mailing\Models\Mails;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use System\Models\File;
use ValidationException;
use System\Classes\MediaLibrary;
use Auth;
use Illuminate\Support\Facades\Mail;
use System\Models\MailSetting;


class Form extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Form Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'unique_emails' => [
                'title' => 'Unique emails',
                'type' => 'checkbox',
                'default' => false,
            ],
        ];
    }

	public function onRun(){
		$user = Auth::getUser();
		if($user){
			$this->page['from_user'] = $user->id;
			$this->page['groups'] = $this->groups();
			$this->page['individuals'] = $this->individuals();
		}else{
			return Redirect::to('/');
		}

	}

	public function groups()
	{
		return Groups::where('type', 1)->orderByRAW('LENGTH(name) ASC')->orderBy('name', 'ASC')->get();
	}

	public function individuals()
	{
		$users = User::where('is_activated', true)->orderBy('name', 'asc')->orderBy('surname', 'asc')->get();
		return $users;
	}

	public function onSubmit(){
		$settings = MailSetting::instance();
		$user = Auth::getUser();

		if(!$user->id){
			return Redirect::to('/');
		}
    	$validator = Validator::make(
    		$form = Input::all(), [
				'subject' => 'required',
				'message' => 'required',
				'users' => 'required_without:groups',
				'groups' => 'required_without:users',
				'attachments' => 'max:2000',
			]
		);

    	if($validator->fails()){
    		throw new ValidationException($validator);
		}

		$users = Input::get('users') ?: [];
		$groups = Input::get('groups') ?: [];
		$subject = Input::get('subject');
		$messageBody = Input::get('message');
		$fromUserId = (int)Input::get('from_user');
		$attachments = Input::file('attachments');
		if($attachments){
			foreach($attachments as $file){
				$maxFileSize = $file->getMaxFilesize();
				$file_name = $file->getClientOriginalName();
				$file_size = $file->getClientSize();
				$content_type = $file->getMimeType();
				if($file->getClientSize() > 3807119){
					Flash::error($file_name.' is too big!');
					return;
				}

			}
		}

//		// get mail data
		$usersData = User::whereIn('id', $users)->get()->toArray();

		if($this->property('unique_emails')){
            $groupsData = Groups::join('pensoft_mailing_groups_users', 'pensoft_mailing_groups_users.groups_id', '=', 'pensoft_mailing_groups.id')
                ->join('users', 'pensoft_mailing_groups_users.user_id', '=', 'users.id')
                ->whereIn('pensoft_mailing_groups.id', $groups)
                ->get()->toArray();
        }else{
            $groupsData = Groups::whereIn('id', $groups)->get()->toArray();
        }

		$senderData = User::where('id', $fromUserId)->first()->toArray();

		$recipients = array_merge($usersData, $groupsData);

        if($this->property('unique_emails')) {
            $resArray = [];
            foreach ($recipients as $val) {
                if (empty($resArray)) {
                    array_push($resArray, $val);
                } else {
                    $value = array_column($resArray, 'email');
                    if (!in_array($val['email'], $value)) {
                        array_push($resArray, $val);
                    }
                }
            }
            $recipients = $resArray;
        }

		foreach($recipients as $mailData){
			$recipientEmail = trim($mailData['email']);
			$recipientName = $mailData['name'].' '. ($mailData['surname'] ?? null);
			$vars = [];
			Mail::send(['raw' => '<div>'.$messageBody.'</div>'], $vars, function($message)  use ($recipientEmail, $recipientName, $subject, $senderData, $attachments, $settings) {
				$message->from($settings->sender_email, $senderData['name'].' '.$senderData['surname']. ' via ' . $settings->sender_name);
				$message->replyTo($senderData['email'], $senderData['name'].' '.$senderData['surname']);
				$message->to($recipientEmail, $recipientName);
				$message->subject($subject);
				$filesSize = 0;
				if($attachments) {
					foreach ($attachments as $file) {
						$maxFileSize = $file->getMaxFilesize();
						$file_name = $file->getClientOriginalName();
						$file_size = $file->getClientSize();
						$content_type = $file->getMimeType();
						$filesSize += $file_size;
						if ($file->getClientSize() > 3807119) {
							return Flash::error($file_name . ' is too big!');
							continue;
						}

						$message->attach($file->getRealPath(), ['as' => $file_name, 'mime' => $content_type]);
					}
				}
			});

			if (count(Mail::failures()) > 0){
				Flash::error('Mail not sent');
				return;
			}
		}

		$mail = new Mails();
		$mail->subject = $subject;
		$mail->user = $users;
		$mail->group = $groups;
		$mail->from_user = $fromUserId;
		$mail->body = $messageBody;
		$mail->attachments = $attachments;

		$mail->save();

		Flash::success('Mail sent');
	}

	public function onFileUpload(){
    	$formData =  Input::all();
		$files = $formData['attachments'];
		$output = '';
		foreach ($files as $f) {
			$file = (new File())->fromPost($f);
			if($file->getExtension() == 'docx' || $file->getExtension() == 'doc'){
				$mediaFileName = 'files_doc.svg';
			}else if($file->getExtension() == 'pdf'){
				$mediaFileName = 'files_pdf.svg';
			}else{
				$mediaFileName = 'files_file.svg';
			}
			$output .= '<img src="' . MediaLibrary::url($mediaFileName) . '" style="width: 30px; float: left; margin-right: 8px;"> '. $file->getFilename().' <br>';
		}

		return  [
			'#attachmentsPreview' => $output
		];
	}
}
