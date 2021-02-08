<?php namespace Pensoft\Mailing;

use Backend;
use RainLab\User\Models\User;
use System\Classes\PluginBase;

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
		return [
			'pensoft.mailing::mail.content' => 'pensoft.mailing::lang.mail.templates.content',
		];
	}

	public function boot(){

	}
}
