<?php

class WebAppPageControllerExtension extends Extension
{

    public function onAfterInit()
    {
        $config = WebAppConfig::current_site_config();
        $icons = $config->WebAppIcons();
        $splashScreens = $config->WebAppStartupScreens();

        $tags = '';
        $tags .= '<meta name="viewport" content="initial-scale=1, user-scalable='.$config->UserScalable.$config->MinimalUI.'">';
        $tags .= '<meta name="apple-mobile-web-app-capable" content="'.$config->Fullscreen.'">';
        $tags .= '<meta name="apple-mobile-web-app-status-bar-style" content="'.$config->StatusBar.'">';
        $tags .= '<meta name="apple-mobile-web-app-title" content="'.$config->AppTitle.'">';

        // User has defined a theme color.
        if($config->ThemeColor) {

            $color = $config->ThemeColor;

            // Make sure color starts with a number sign.
            if(substr($color,0,1) !== '#') $color = '#' . $color;

            // Chrome, Firefox, Opera
            $tags .= '<meta name="theme-color" content="' . $color . '">';

            // Windows Phone
            $tags .= '<meta name="msapplication-navbutton-color" content="' . $color . '">';

        }
        
        foreach ($icons as $icon) {
            $size = $icon->Size;
            $url = $icon->Image()->URL;
            $tags .= '<link href="'.$url.'" sizes="'.$size.'" rel="apple-touch-icon">';
        }

        foreach ($splashScreens as $splashScreen) {
            $media = $splashScreen->Media;
            $url = $splashScreen->Image()->URL;
            $tags .= '<link href="'.$url.'" media="'.$media.'" rel="apple-touch-startup-image">';
        }

        Requirements::insertHeadTags($tags);
    }
}
