<?php namespace Pensoft\Mailing\Models;

use Illuminate\Support\Facades\DB;
use Model;

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

	public function beforeSave(){
        $alias = new Alias();


//		array:7 [
//			"id" => 6
//			  "name" => "WP2"
//			  "email" => "WP2@pensoft.net"
//			  "created_at" => "2021-02-04 12:15:32"
//			  "updated_at" => "2021-02-04 12:15:32"
//			  "type" => "1"
//			  "user_id" => null
//			]
		$lGroupData = $this->attributes;
		dd($lGroupData['email']);
		//SELECT * FROM EditEmailGroup(\'' . $lGroupData['email'] . '\', \'' . q(trim($lGroupData['groupmembers'])) . '\', \'' . $lGroupDomain . '\',  \'' . q(trim($lGroupData['groupmoderators'])) . '\')

		// $data = Db::connection('pgsql_vmail')->select('SELECT * from alias');
//		$pdo = Db::connection()->getPdo();
		// dd($data); die;
		 $data = DB::connection('vmail')->select('SELECT * FROM EditEmailGroup(\'' . $lGroupData['email'] . '\', \'' . q(trim($lGroupData['groupmembers'])) . '\', \'' . $lGroupDomain . '\',  \'' . q(trim($lGroupData['groupmoderators'])) . '\')');
		 dd($data); die;
	}
}
