<?php namespace Pensoft\Mailing\Models;

use Model;
use RainLab\User\Models\User;

/**
 * Mails Model
 */
class Mails extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'pensoft_mailing_mails';

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
    public $belongsTo = [
		'sender' => ['Rainlab\User\Models\User', 'key' => 'from_user']
	];
	public $belongsToMany = [
		'user' => [
			'Rainlab\User\Models\User',
			'table' => 'pensoft_mailing_mails_users',
			'order' => 'name'
		],
		'group' => [
			'Pensoft\Mailing\Models\Groups',
			'table' => 'pensoft_mailing_mails_groups',
			'order' => 'name'
		],
	];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [
    	'attachments' => 'System\Models\File'
	];

    public function getUserOptions(){
		return User::where('id', 6);
	}
}
