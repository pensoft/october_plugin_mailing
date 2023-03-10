<?php namespace Pensoft\Mailing;

use Backend;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\Event;
use Pensoft\Mailing\Models\Groups;
use RainLab\User\Controllers\Users;
use RainLab\User\Models\User;
use System\Classes\PluginBase;
use Illuminate\Support\Facades\DB;
use SaurabhDhariwal\Revisionhistory\Classes\Diff as Diff;
use System\Models\Revision as Revision;


/**
 * Mailing Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Mailing',
            'description' => 'No description provided yet...',
            'author'      => 'Pensoft',
            'icon'        => 'icon-envelope'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {

        return [
            'Pensoft\Mailing\Components\Form' => 'MailingForm',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {

        return [
            'pensoft.mailing.some_permission' => [
                'tab' => 'Mailing',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
		return [
			'mailing' => [
				'label' => "Mailing",
				'url' => Backend::url('pensoft/mailing/groups'),
				'permissions' => ['pensoft.mailing.*'],
				'icon' => 'icon-envelope',
				'sideMenu' => [
					'groups' => [
						'label' => 'Groups',
						'icon'  => 'icon-group',
						'url'   => Backend::url('pensoft/mailing/groups'),
						'permissions' => ['pensoft.mailing.*']
					],
					'mails' => [
						'label' => 'Mails',
						'icon'  => 'icon-envelope-o',
						'url'   => Backend::url('pensoft/mailing/mails'),
						'permissions' => ['pensoft.mailing.*']
					],
				]
			]
		];
    }

	/**
	 * Register Plugin Mail Templates
	 *
	 * @return array
	 */
	public function registerMailTemplates()
	{

	}


	public function boot()

	{

        /* Extetions for revision */
        Revision::extend(function($model){
            /* Revison can access to the login user */
            $model->belongsTo['user'] = ['Backend\Models\User'];

            /* Revision can use diff function */
            $model->addDynamicMethod('getDiff', function() use ($model){
                return Diff::toHTML(Diff::compare($model->old_value, $model->new_value));
            });
        });

		//Extending User Model and add mailing group relation
		User::extend(function($model) {
			$theme = Theme::getActiveTheme();
            $model->belongsToMany['mailing_groups'] = [
				'Pensoft\Mailing\Models\Groups',
				'table' => 'pensoft_mailing_groups_users',
				'order' => 'name'
			];
			if (!$model instanceof User) return;

			$model->bindEvent('model.beforeSave', function() use ($model) {
				if (!isset($model->trigger_backend_save_hack)) return;
				$model->trigger_backend_save = 1;
				unset($model->trigger_backend_save_hack);
			});
			//When editing user details and add/remove user from groups - alias table needs to be updated accordingly.
			//Every time when adding user - update "moderators" column in alias for ALL groups from the domain.
			$model->bindEvent('model.afterSave', function() use ($model, $theme) {
				if (!$model->trigger_backend_save) return;
				$userId = $model->id;
				$userData = User::find($userId);
				$userData->trigger_backend_save = 0;
				$userData->save();

				$domain = $theme->site_domain ?? $_SERVER['SERVER_NAME'];


				$users = User::get()->toArray();
				$groupModerators = 'noreply@'. $domain .', root@psweb.pensoft.net, messaging@pensoft.net';
				foreach ($users as $user){
					$groupModerators .= ', ' . $user['email'];
				}
				$userMailingGroupsData = $model->find($userId)->mailing_groups;

				// remove user from all groups before adding to the selected
				$pensoftMailingGroups = Groups::get();
				foreach ($pensoftMailingGroups as $pensoftMailingGroup){
					$PensoftGroupEmail = strtolower($pensoftMailingGroup->email);
					$PensoftGroupDomain = explode('@', $PensoftGroupEmail)[1];
					$groupUsersArr = $pensoftMailingGroup->user->toArray();
					$arr = [];
					foreach ($groupUsersArr as $key => $uDataArr){
						if($uDataArr['id'] != $userId){
							$arr[] = $uDataArr['email'];
						}

					}
					$PensoftGroupMembers = implode(', ', $arr);
					DB::connection('vmail')->select('SELECT * FROM EditEmailGroup(\'' . $PensoftGroupEmail . '\', \'' . trim($PensoftGroupMembers) . '\', \'' . $PensoftGroupDomain . '\',  \'' . trim($groupModerators) . '\')');
				}

				foreach ($userMailingGroupsData as $MailingGroup){
					$lGroupData = $MailingGroup->attributes;

					$group = Groups::find($lGroupData['id']);
					$groupMembersArr = [];
					foreach ($group->user as $user) {
						$groupMembersArr[] = $user->attributes['email'];
					}
					$groupMembers = implode( ', ', $groupMembersArr);
					$groupEmail = strtolower($lGroupData['email']);
					$groupDomain = explode('@', $groupEmail)[1];
					DB::connection('vmail')->select('SELECT * FROM EditEmailGroup(\'' . $groupEmail . '\', \'' . trim($groupMembers) . '\', \'' . $groupDomain . '\',  \'' . trim($groupModerators) . '\')');

				}
			});

			//Every time when deleting user - update "moderators" and "goto" column in alias for ALL groups from the domain.
			$model->bindEvent('model.beforeDelete', function() use ($model, $theme) {

				$userId = $model->id;
				$userEmail = $model->email;
				$domain = $theme->site_domain ?? $_SERVER['SERVER_NAME'];
				$groupModerators = 'noreply@'. $domain .', root@psweb.pensoft.net, messaging@pensoft.net';
				$users = User::get()->toArray();
				foreach ($users as $user){
					if($user['id'] != $userId){
						$groupModerators .= ', ' . $user['email'];
					}
				}

				// remove user from all groups before adding to the selected
				$pensoftMailingGroups = Groups::get();
				foreach ($pensoftMailingGroups as $pensoftMailingGroup){
					$PensoftGroupEmail = $pensoftMailingGroup->email;
					$PensoftGroupDomain = explode('@', $PensoftGroupEmail)[1];
					$groupUsersArr = $pensoftMailingGroup->user->toArray();
					$arr = [];
					foreach ($groupUsersArr as $key => $uDataArr){
						if($uDataArr['id'] != $userId){
							$arr[] = $uDataArr['email'];
						}

					}
					$PensoftGroupMembers = implode( ', ', $arr);
					DB::connection('vmail')->select('SELECT * FROM EditEmailGroup(\'' . $PensoftGroupEmail . '\', \'' . trim($PensoftGroupMembers) . '\', \'' . $PensoftGroupDomain . '\',  \'' . trim($groupModerators) . '\')');
				}
			});

        });

		//Extending User Plugin FormFields and add the mailing group field
		if (class_exists('\Rainlab\User\Controllers\Users') && class_exists('\Pensoft\Mailing\Models\Groups')) {
			\Rainlab\User\Controllers\Users::extendFormFields(function ($form, $model, $context) {
				if (!$model instanceof User) return;

				$form->addTabFields([
					'mailing_groups' => [
						'label' => 'Pensoft Mailing Group',
						'emptyOption' => '-- choose --',
						'span'  => 'auto',
						'type'  => 'relation',
						'select'  => 'CONCAT(name, \' - \', email)',
						'tab'  => 'rainlab.user::lang.user.account',
						'options' => Models\Groups::all()->lists('user', 'id'),
						'nameFrom' => 'user'
					],
					'trigger_backend_save_hack' => [
						'label' => '',
						'span'  => 'auto',
						'type'  => 'text',
						'tab'  => 'rainlab.user::lang.user.account',
//						'hidden' => 'true',
						'default' => '1',
						'value' => '1',
						'attributes' => [
							'style' => 'display:none;'
						]
					]
				]);

			});
		}



	}

}
