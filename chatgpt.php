<?php
declare(strict_types = 1);

/**
 * @copyright Copyright (c) 2023  R2H BV (https://r2h.nl). All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Application\CMSApplication;

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
     * @return Registry|void  The button options as CMSObject, void if ACL check fails.
     *
     * @since 1.5
     */
    public function onDisplay(string $name): ?Registry
    {
        $app = Factory::getApplication();

        assert($app instanceof CMSApplication, new \RuntimeException('Application is not CMSApplication', 500));

        $user  = $app->getIdentity();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values = (array) $app->getUserState('com_content.edit.article.id');
        $isEditingRecords = count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return null;
        }

        $data = [
            'apiKey' => $this->params->get('apikey', ''),
            'apiModel' => $this->params->get('model', 'text-davinci-003'),
            'temp' => $this->params->get('temp', '0.5'),
            'apiTokenLow' => $this->params->get('tokenLow', '1000'),
            'apiTokenHi' => $this->params->get('tokenHi', '2000'),
        ];

        try {
            [
                'apiKey' => $apiKey,
                'apiModel' => $apiModel,
                'temp' => $temp,
                'apiTokenLow' => $apiTokenLow,
                'apiTokenHi' => $apiTokenHi,
            ] = $this->validate([
                'apiKey' => ['required', 'string'],
                'apiModel' => [
                    'required',
                    'string',
                    'in:text-davinci-003,text-curie-001,text-babbage-001,text-ada-001',
                ],
                'temp' => ['required', 'numeric'],
                'apiTokenLow' => ['required', 'numeric', 'min:1', 'max:2048'],
                'apiTokenHi' => ['required', 'numeric', 'min:1', 'max:4000'],
            ], $data);
        } catch (Exception $e) {
            return null;
        }

        $tokens = ($apiModel === 'text-davinci-003') ? $apiTokenHi : $apiTokenLow;

        $doc = $this->app->getDocument();

        $doc->getWebAssetManager()
            ->registerAndUseScript('chatgpt', 'plg_editors-xtd_chatgpt/chatgpt-default.js', [], ['defer' => true]);

        // Pass some data to javascript
        $doc->addScriptOptions(
            'xtd-chatgpt',
            [
                'apikey' => $apiKey,
                'model' => $apiModel,
                'temp' => $temp,
                'tokens' => $tokens,
                'waitingmsg' => Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_WAITING'),
                'errormsg' => Text::_('PLG_EDITORS-XTD_CHATGPT_ERROR'),
            ]
        );

        $button = new stdClass;
        $button->modal   = false;
        $button->text    = Text::_('PLG_EDITORS-XTD_CHATGPT_BTNTITLE');
        $button->name    = $this->_type . '_' . $this->_name;
        $button->onclick = 'chatgtpPopup(\'' . $name . '\');return false;';
        $button->icon    = 'chatgpt-logo';
        $button->iconSVG = file_get_contents(__DIR__ . '/assets/chatgpt-logo.svg');
        $button->link    = '#';

        return new Registry($button);
    }

    /**
     * Listener for the `onBeforeRender` event.
     *
     * @since  1.0
     */
    public function onBeforeRender(): void
    {
        $app = Factory::getApplication();

        assert($app instanceof CMSApplication, new \RuntimeException('Application is not CMSApplication', 500));

        $user  = $app->getIdentity();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values = (array) $app->getUserState('com_content.edit.article.id');
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
        $app = Factory::getApplication();

        assert($app instanceof CMSApplication, new \RuntimeException('Application is not CMSApplication', 500));

        $user  = $app->getIdentity();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values = (array) $app->getUserState('com_content.edit.article.id');
        $isEditingRecords = count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return;
        }

        $data = [
            'apiModel' => $this->params->get('model', 'text-davinci-003'),
            'temp' => $this->params->get('temp', '0.5'),
            'apiTokenLow' => $this->params->get('tokenLow', '1000'),
            'apiTokenHi' => $this->params->get('tokenHi', '2000'),
        ];

        try {
            [
                'apiModel' => $apiModel,
                'temp' => $apiTemp,
                'apiTokenLow' => $apiTokenLow,
                'apiTokenHi' => $apiTokenHi,
            ] = $this->validate([
                'apiModel' => [
                    'required',
                    'string',
                    'in:text-davinci-003,text-curie-001,text-babbage-001,text-ada-001',
                ],
                'temp' => ['required', 'numeric'],
                'apiTokenLow' => ['required', 'numeric', 'min:1', 'max:2048'],
                'apiTokenHi' => ['required', 'numeric', 'min:1', 'max:4000'],
            ], $data);
        } catch (Exception $e) {
            return;
        }

        // Get the body text from the Application.
        $content = $this->app->getBody();

        $newBodyOutput = self::loadLayout('default', [
            'apiModel' => $apiModel,
            'apiTemp' => $apiTemp,
            'tokens' => $apiModel === 'text-davinci-003' ? $apiTokenHi : $apiTokenLow,
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
        $app = Factory::getApplication();

        assert($app instanceof CMSApplication, new \RuntimeException('Application is not CMSApplication', 500));

        $user  = $app->getIdentity();

        // Can create in any category (component permission) or at least in one category
        $canCreateRecords = $user->authorise('core.create', 'com_content')
            || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

        // Instead of checking edit on all records, we can use **same** check as the form editing view
        $values = (array) $app->getUserState('com_content.edit.article.id');
        $isEditingRecords = count($values);

        // This ACL check is probably a double-check (form view already performed checks)
        $hasAccess = $canCreateRecords || $isEditingRecords;
        if (!$hasAccess) {
            return;
        }

        $wa = $app->getDocument()->getWebAssetManager();

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

    /**
     * Validate the ruleset.
     *
     * @param  array<string, array<string>> $ruleset The ruleset to validate.
     * @param  array<string, mixed>         $data    The data to validate.
     * @return array<string, mixed> The validation results.
     */
    protected function validate(array $ruleset, array $data): array
    {
        // Define the rules.
        $definedRules = [
            'required' => function ($value) {
                return strlen($value) > 0;
            },
            'numeric' => function ($value) {
                return is_numeric($value);
            },
            'string' => function ($value) {
                return is_string($value);
            },
            'min' => function ($value, string $min) {
                return $value >= (int) $min;
            },
            'max' => function ($value, string $max) {
                return $value <= (int) $max;
            },
            'in' => function ($value, string $haystack) {
                $haystack = array_map('trim', explode(',', $haystack));
                return in_array(trim($value), $haystack);
            },
        ];

        $items = [];

        foreach ($ruleset as $key => $rules) {
            $value = $this->validateItem($key, $rules, $definedRules, $data);
            $items[$key] = $value;
        }

        return $items;
    }

    /**
     * Validate an item.
     *
     * @param  string                  $key          The key to validate.
     * @param  array<string>           $rules        The rules to validate.
     * @param  array<string, callable> $definedRules The defined rules.
     * @param  array<string, mixed>    $data         The data to validate.
     * @throws \InvalidArgumentException If the rule is not defined.
     *                                   If the rule fails.
     */
    protected function validateItem(string $key, array $rules, array $definedRules, array $data): mixed
    {
        $value = $data[$key] ?? null;

        foreach ($rules as $rule) {
            [$rule, $arguments] = array_pad(explode(':', $rule, 2), 2, null);

            if (!array_key_exists($rule, $definedRules)) {
                throw new \InvalidArgumentException(sprintf('Rule "%s" is not defined', $rule), 402);
            }

            $rule = $definedRules[$rule];

            if (!$rule($value, $arguments)) {
                throw new \InvalidArgumentException(sprintf('Rule "%s" failed for "%s"', $rule, $key), 402);
            }
        }

        return $value;
    }
}
