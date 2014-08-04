<?php

class WebAppPageControllerExtension extends Extension {

	public function onAfterInit(){
		$config = WebAppConfig::current_site_config();
		$icons = $config->WebAppIcons();
		$splashScreens = $config->WebAppStartupScreens();

		$tags = '';
		$tags .= '<meta name="apple-mobile-web-app-capable" content="'.$config->Fullscreen.'">';
		$tags .= '<meta name="apple-mobile-web-app-status-bar-style" content="'.$config->StatusBar.'">';
		$tags .= '<meta name="apple-mobile-web-app-title" content="'.$config->AppTitle.'">';

		foreach($icons as $icon) {
			$size = $icon->Size;
			$url = $icon->Image()->URL;
			$tags .= '<link href="'.$url.'" sizes="'.$size.'" rel="apple-touch-icon">';
		}

		foreach($splashScreens as $splashScreen) {
			$media = $splashScreen->Media;
			$url = $splashScreen->Image()->URL;
			$tags .= '<link href="'.$url.'" media="'.$media.'" rel="apple-touch-startup-image">';
		}

		Requirements::insertHeadTags($tags);
	}
}