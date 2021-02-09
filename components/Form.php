<?php namespace Pensoft\Mailing\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Input;
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
        return [];
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
		return Groups::where('type', 1)->get();
	}

	public function individuals()
	{
		$users = User::where('is_activated', true)->get();
		return $users;
	}

	public function onSubmit(){
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

//		// get mail data
		$usersData = User::whereIn('id', $users)->get()->toArray();
		$groupsData = Groups::whereIn('id', $groups)->get()->toArray();

		$senderData = User::where('id', $fromUserId)->first()->toArray();

		$recipients = array_merge($usersData, $groupsData);

		foreach($recipients as $mailData){
			$recipientEmail = $mailData['email'];
			$vars = [
				'name' => $mailData['name'] .' '. ($mailData['surname'] ?? null), // Dear, Name
				'email' => $mailData['email'],
			];
			Mail::send(['raw' => $messageBody], $vars, function($message)  use ($recipientEmail, $subject, $senderData, $attachments) {
				$message->from($senderData['email'], $senderData['name'].' '.$senderData['surname']);
				$message->to($recipientEmail);
				$message->subject($subject);
				$filesSize = 0;
				foreach($attachments as $file){
					$maxFileSize = $file->getMaxFilesize();
					$file_name = $file->getClientOriginalName();
					$file_size = $file->getClientSize();
					$content_type = $file->getMimeType();
					$filesSize += $file_size;

					$message->attach($file->getRealPath(), ['as' => $file_name, 'mime' => $content_type]);
				}
//				dd($maxFileSize, $filesSize);
			});

			if (count(Mail::failures()) > 0){
				return Flash::error('Mail not sent');
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
