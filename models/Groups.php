<?php namespace Pensoft\Mailing\Models;

use Backend\Facades\BackendAuth;
use Illuminate\Support\Facades\DB;
use Model;
use RainLab\User\Models\User;
use System\Models\MailSetting;

/**
 * Groups Model
 */
class Groups extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'pensoft_mailing_groups';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [
    	'user' => [
    		'Rainlab\User\Models\User',
			'table' => 'pensoft_mailing_groups_users',
			'order' => 'name'
		]
	];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

	public function afterSave(){
		$settings = MailSetting::instance();

		$users = User::get()->toArray();
		$groupModerators = $settings->sender_email . ', root@psweb.pensoft.net, messaging@pensoft.net';
		foreach ($users as $user){
			$groupModerators .= ', ' . $user['email'];
		}

		$lGroupData = $this->attributes;

		$group = Groups::find($lGroupData['id']);
		$groupMembersArr = [];
		foreach ($group->user as $user) {
			$groupMembersArr[] = $user->attributes['email'];
		}

		$groupMembers = implode( ', ', $groupMembersArr);
		$groupEmail = strtolower($lGroupData['email']);
		$groupDomain = explode('@', $groupEmail)[1];

		DB::connection('vmail')->select('SELECT * FROM EditEmailGroup(\'' . $groupEmail . '\', \'' . trim($groupMembers) . '\', \'' . $groupDomain . '\',  \'' . trim($groupModerators) . '\')');

        $replaceFrom = $this->replace_from;
        $replaceTo = $this->replace_to;
        $nameAppend = $this->name_append;
        $addReplyTo = $this->add_reply_to;

        DB::connection('vmail')->select('SELECT * FROM savereplaceoptions(\'' . $groupEmail . '\', \'' . trim($replaceFrom) . '\', \'' . trim($replaceTo) . '\', \'' . trim($nameAppend) . '\', \'' . trim($addReplyTo) . '\', 1)');
	}

    public function filterFields($fields, $context = null){
        $user = BackendAuth::getUser();
        if(!$user->is_superuser){
            $fields->replace_from->disabled = true;
            $fields->replace_to->disabled = true;
            $fields->name_append->disabled = true;
            $fields->add_reply_to->disabled = true;
        }
    }
}
