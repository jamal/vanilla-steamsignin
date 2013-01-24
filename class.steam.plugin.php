<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['SteamSignIn'] = array(
	'Name' => 'Steam Sign In',
   'Description' => 'Allows users to sign in with their Steam accounts. Requires &lsquo;OpenID&rsquo; plugin to be enabled first.',
   'Version' => '1.2',
   'RequiredApplications' => array('Vanilla' => '2.1a'),
   'RequiredPlugins' => array('OpenID' => '0.2'),
   'RequiredTheme' => FALSE,
	'MobileFriendly' => TRUE,
   'SettingsUrl' => '/dashboard/plugin/steamsignin',
   'SettingsPermission' => 'Garden.Settings.Manage',
   'HasLocale' => TRUE,
   'RegisterPermissions' => FALSE,
   'Author' => "Jamal Fanaian",
   'AuthorEmail' => 'j@jamalfanaian.com',
   'AuthorUrl' => 'http://jamalfanaian.com'
);

class SteamSignInPlugin extends Gdn_Plugin {

   /// Properties ///

   protected function _AuthorizeHref($Popup = FALSE) {
      $Url = Url('/entry/openid', TRUE);
      $UrlParts = explode('?', $Url);
      parse_str(GetValue(1, $UrlParts, ''), $Query);

      $Query['url'] = 'http://steamcommunity.com/openid';
      $Path = '/'.Gdn::Request()->Path();
      $Query['Target'] = GetValue('Target', $_GET, $Path ? $Path : '/');
      if ($Popup)
         $Query['display'] = 'popup';

       $Result = $UrlParts[0].'?'.http_build_query($Query);
      return $Result;
   }
   
   /**
    * Act as a mini dispatcher for API requests to the plugin app
    */
   public function PluginController_SteamSignIn_Create(&$Sender) {
      $Sender->Permission('Garden.Settings.Manage');
		$this->Dispatch($Sender, $Sender->RequestArgs);
   }
   
   public function Controller_Toggle($Sender) {
      $this->AutoToggle($Sender);
   }
   
   public function AuthenticationController_Render_Before($Sender, $Args) {
      if (isset($Sender->ChooserList)) {
         $Sender->ChooserList['steamsignin'] = 'Steam';
      }
      if (is_array($Sender->Data('AuthenticationConfigureList'))) {
         $List = $Sender->Data('AuthenticationConfigureList');
         $List['steamsignin'] = '/dashboard/plugin/steamsignin';
         $Sender->SetData('AuthenticationConfigureList', $List);
      }
   }

   /// Plugin Event Handlers ///

   /**
    *
    * @param Gdn_Controller $Sender
    */
   public function EntryController_SignIn_Handler($Sender, $Args) {
      if (!$this->IsEnabled()) return;
      
      if (isset($Sender->Data['Methods'])) {
         $ImgSrc = Asset('/plugins/SteamSignIn/design/steam-signin.png');
         $ImgAlt = T('Sign In with Steam');
         $SigninHref = $this->_AuthorizeHref();
         $PopupSigninHref = $this->_AuthorizeHref(TRUE);

         // Add the twitter method to the controller.
         $Method = array(
            'Name' => 'Steam',
            'SignInHtml' => "<a id=\"SteamAuth\" href=\"$SigninHref\" class=\"PopupWindow\" popupHref=\"$PopupSigninHref\" popupHeight=\"600\" popupWidth=\"800\" ><img src=\"$ImgSrc\" alt=\"$ImgAlt\" /></a>");

         $Sender->Data['Methods'][] = $Method;
      }
   }

   public function Base_BeforeSignInButton_Handler($Sender, $Args) {
      if (!$this->IsEnabled()) return;
		echo "\n".$this->_GetButton();
	}
	
	private function _GetButton() {      
      $ImgSrc = Asset('/plugins/SteamSignIn/design/steam-icon.png');
      $ImgAlt = T('Sign In with Steam');
      $SigninHref = $this->_AuthorizeHref();
      $PopupSigninHref = $this->_AuthorizeHref(TRUE);
      return "<a id=\"SteamAuth\" href=\"$SigninHref\" class=\"PopupWindow\" title=\"$ImgAlt\" popupHref=\"$PopupSigninHref\" popupHeight=\"600\" popupWidth=\"800\" ><img src=\"$ImgSrc\" alt=\"$ImgAlt\" /></a>";
   }
	
	public function Base_BeforeSignInLink_Handler($Sender) {
      if (!$this->IsEnabled())
			return;

		if (!Gdn::Session()->IsValid())
			echo "\n".Wrap($this->_GetButton(), 'li', array('class' => 'Connect SteamConnect'));
	}
}
