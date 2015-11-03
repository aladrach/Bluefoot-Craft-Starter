<?php

namespace Craft;

class VentiPlugin extends BasePlugin
{
    public function getName()
    {
        return Craft::t('Venti');
    }

    public function getVersion()
    {
        return '0.9.2';
    }

    public function getDeveloper()
    {
        return 'Tipping Media';
    }

    public function getDeveloperUrl()
    {
        return 'http://tippingmedia.com';
    }

    public function addTwigExtension()
    {
        Craft::import('plugins.venti.twigextensions.VentiTwigExtension');
        return new VentiTwigExtension();
    }

    public function getSettingsHtml() {
        return craft()->templates->render(
          'venti/settings'
        );
    }


}
