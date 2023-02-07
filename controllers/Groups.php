<?php namespace Pensoft\Mailing\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\DB;
use Pensoft\Mailing\Models\Groups as GroupsModel;
use RainLab\User\Models\User;

/**
 * Groups Back-end Controller
 */
class Groups extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    /**
     * @var string Configuration file for the `FormController` behavior.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string Configuration file for the `ListController` behavior.
     */
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Pensoft.Mailing', 'mailing', 'groups');
    }

    public function onSync() {
        $theme = Theme::getActiveTheme();
        //update from alias table
        $aliasData = DB::connection('vmail')->select('SELECT * FROM alias a LEFT JOIN list_replace_options lro ON lro.list_address = a.address WHERE a.domain = \'' . trim($theme->site_domain ?? $_SERVER['SERVER_NAME']) . '\' AND a.islist = 1');
        if(count($aliasData)) {
            foreach ($aliasData as $alias) {
                $groups = GroupsModel::where('email', $alias->address)->first();

                if(!$groups){
                    $groups = new GroupsModel();
                }
                $groups->name = $alias->address;
                $groups->email = $alias->address;
                $groups->type = 1;
                $groups->replace_from = $alias->replace_from;
                $groups->replace_to = $alias->replace_to;
                $groups->name_append = $alias->name_append;
                $groups->add_reply_to = $alias->add_reply_to;

                $groups->save();

                $arrGoTo = explode(',', $alias->goto);
                foreach ($arrGoTo as $checkUserMail) {
                    $user = User::where('email', $checkUserMail)->first();
                    if ($user){
                        if(!$user->mailing_groups()->find($groups->id)){
                            $user->mailing_groups()->attach($groups);
                            $user->save();
                        }
                    }
                }


            }
        }
        \Flash::info('Synced with vmail!');
        return $this->asExtension('ListController')->listRefresh();

    }

}
