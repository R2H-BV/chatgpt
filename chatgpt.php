<?php
declare(strict_types = 1);

/**
 * @copyright Copyright (c) 2023  R2H BV (https://r2h.nl). All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

 use Joomla\CMS\Factory;
 use Joomla\CMS\Language\Text;
 use Joomla\CMS\HTML\HTMLHelper;
 use Joomla\CMS\Object\CMSObject;
 use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 *  editorsxtd - chatgpt Plugin
 *
 * @package   Joomla.Plugin
 * @subpakage R2H B.V. chatgpt
 */
class PlgEditorsXtdChatgpt extends CMSPlugin
{
    /**
     * Application object.
     *
     * @var   CMSApplicationInterface
     */
    protected $app;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var   boolean
     */
    protected $autoloadLanguage = true;

    /**
     * Display the button
     *
     * @param  string $name The name of the button to add.
     * @return CMSObject|void  The button options as CMSObject, void if ACL check fails.
     *
     * @since 1.5
     */
    public function onDisplay(string $name): ?CMSObject
    {
        $user  = Factory::getUser();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
        $isEditingRecords = count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return null;
        }

        $apiKey = $this->params->get('apikey', '');
        $apiModel = $this->params->get('model', 'text-davinci-003');
        (float) $apiTemp = $this->params->get('temp', '0.5');
        (int) $apitokenLow = $this->params->get('tokenLow', '1000');
        (int) $apitokenHi = $this->params->get('tokenHi', '2000');

        if ($apitokenLow < 1 || $apitokenLow > 2048) {
            $apitokenLow = 1000;
        }

        if ($apitokenHi < 1 || $apitokenHi > 4000) {
            $apitokenHi = 2000;
        }

        if ($apiModel === 'text-davinci-003') {
            $tokens = $apitokenHi;
        } else {
            $tokens = $apitokenLow;
        }

        if (!$apiKey) {
            return null;
        }

        $doc = $this->app->getDocument();

        $doc->getWebAssetManager()
            ->registerAndUseScript('chatgpt', 'plg_editors-xtd_chatgpt/chatgpt-default.js', [], ['defer' => true]);

        // Pass some data to javascript
        $doc->addScriptOptions(
            'xtd-chatgpt',
            [
                'apikey' => $apiKey,
                'model' => $apiModel,
                'temp' => $apiTemp,
                'tokens' => $tokens,
                'waitingmsg' => Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_WAITING'),
                'errormsg' => Text::_('PLG_EDITORS-XTD_CHATGPT_ERROR'),
            ]
        );

        $button = new CMSObject();
        $button->modal   = false;
        $button->text    = Text::_('PLG_EDITORS-XTD_CHATGPT_BTNTITLE');
        $button->name    = $this->_type . '_' . $this->_name;
        $button->onclick = 'chatgtpPopup(\'' . $name . '\');return false;';
        $button->icon    = 'chatgpt-logo';
        $button->iconSVG = file_get_contents(__DIR__ . '/assets/chatgpt-logo.svg');
        $button->link    = '#';

        return $button;
    }

    /**
     * Listener for the `onBeforeRender` event.
     *
     * @since  1.0
     */
    public function onBeforeRender(): void
    {
        $user  = Factory::getUser();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
        $isEditingRecords = count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return;
        }

        // Load the Bootstrap modal JS.
        HTMLHelper::_('bootstrap.modal');
    }

    /**
     * Listener for the `onAfterRender` event.
     *
     * @since  1.0
     */
    public function onAfterRender(): void
    {
        $user  = Factory::getUser();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
        $isEditingRecords = count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return;
        }

        $apiModel = $this->params->get('model', 'text-davinci-003');
        (float) $apiTemp = $this->params->get('temp', '0.5');
        (int) $apitokenLow = $this->params->get('tokenLow', '1000');
        (int) $apitokenHi = $this->params->get('tokenHi', '2000');

        if ($apitokenLow < 1 || $apitokenLow > 2048) {
            $apitokenLow = 1000;
        }

        if ($apitokenHi < 1 || $apitokenHi > 4000) {
            $apitokenHi = 2000;
        }

        if ($apiModel === 'text-davinci-003') {
            $tokens = $apitokenHi;
        } else {
            $tokens = $apitokenLow;
        }

        // Get the body text from the Application.
        $content = $this->app->getBody();

        $newBodyOutput = self::loadLayout('default', [
            'apiModel' => $apiModel,
            'apiTemp' => $apiTemp,
            'tokens' => $tokens,
        ]);


        // Replace the closing body tag with form.
        $buffer = str_ireplace('</body>', $newBodyOutput . '</body>', $content);

        // Output the buffer.
        $this->app->setBody($buffer);
    }

    /**
     * Triggered before compiling the head.
     */
    public function onBeforeCompileHead(): void
    {
        $user  = Factory::getUser();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
        $isEditingRecords = count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return;
        }

        /**
         * @var Joomla\CMS\WebAsset\WebAssetManager $wa
         */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        // Load CSS
        $wa->registerAndUseStyle('chatgpt', 'plg_editors-xtd_chatgpt/chatgpt-default.css', [], ['as'=>'style']);
    }

    /**
     * Load a layout file.
     *
     * @param  string       $layout The layout file to load.
     * @param  array<mixed> $vars   An array of variables to pass to the layout file.
     * @param  string       $path   The path to the layout file.
     * @throws \RuntimeException If the layout file cannot be found or output buffer fails.
     */
    protected static function loadLayout(string $layout, array $vars = [], ?string $path = null): string
    {
        $layout = $layout ? $layout : 'default';

        $path = $path ? $path : __DIR__ . '/tmpl';

        $file = $path . '/' . $layout . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('Layout "%s" not found', $layout), 500);
        }

        extract($vars, EXTR_SKIP);

        ob_start();

        include $file;

        $buffer = ob_get_clean();

        if ($buffer === false) {
            throw new \RuntimeException(sprintf('Layout "%s" rendering failed', $layout), 500);
        }

        return $buffer;
    }
}
