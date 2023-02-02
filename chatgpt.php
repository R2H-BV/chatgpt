<?php
/**
 * @copyright	Copyright (c) 2023  R2H BV (https://www.r2h.nl). All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

 use Joomla\CMS\Factory;
 use Joomla\CMS\Language\Text;
 use Joomla\CMS\Object\CMSObject;
 use Joomla\CMS\Plugin\CMSPlugin;
 use Joomla\CMS\Session\Session;
 use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 *  editorsxtd - chatgpt Plugin
 *
 * @package		Joomla.Plugin
 * @subpakage	R2H B.V. chatgpt
 */
class plgEditorsXtdChatgpt extends CMSPlugin {

	/**
	 * Application object.
	 *
	 * @var    CMSApplicationInterface
	 * @since  4.1.0
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  4.1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  CMSObject|void  The button options as CMSObject, void if ACL check fails.
	 *
	 * @since   1.5
	 */
	public function onDisplay($name)
	{
	// Check if administrator of site
	if (!$this->app->isClient('administrator') || $this->app->input->get->getString('view') !== 'article') {
		return;
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

		if ($apiModel == 'text-davinci-003') {
				$tokens = $apitokenHi;
		} else {
				$tokens = $apitokenLow;
		}

		if (!$apiKey) {
				return;
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
				'waitingmsg' => Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_WAITING')
			]
		);

		$button = new CMSObject();
		$button->modal   = false;
		$button->text    = Text::_('PLG_EDITORS-XTD_CHATGPT_BTNTITLE');
		$button->name    = $this->_type . '_' . $this->_name;
		$button->onclick = 'chatgtpPopup(\'' . $name . '\');return false;';
		$button->icon    = 'chatgpt-logo';
		$button->iconSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 30 30" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" xmlns:v="https://vecta.io/nano"><g transform="translate(-.998 -1)"><clipPath id="A"><path d="M.998 1h29.133v29.53H.998z"/></clipPath><g clip-path="url(#A)"><path d="M28.21 13.08c.67-2.01.44-4.21-.63-6.04a7.46 7.46 0 0 0-8.01-3.57A7.39 7.39 0 0 0 14.02 1a7.47 7.47 0 0 0-7.1 5.15A7.38 7.38 0 0 0 2 9.72a7.44 7.44 0 0 0 .92 8.72 7.33 7.33 0 0 0 .63 6.03c1.61 2.81 4.85 4.25 8.02 3.58 1.4 1.58 3.42 2.49 5.54 2.48a7.47 7.47 0 0 0 7.1-5.15 7.34 7.34 0 0 0 4.91-3.57 7.42 7.42 0 0 0-.91-8.72v-.01zm-2.3-5.07c.64 1.12.88 2.43.66 3.7-.04-.03-.12-.07-.17-.1l-5.88-3.4c-.3-.17-.67-.17-.97 0l-6.89 3.98V9.27l5.69-3.29c2.65-1.53 6.03-.62 7.56 2.03zm-13.25 6.07l2.9-1.68 2.9 1.68v3.35l-2.9 1.68-2.9-1.68v-3.35zm1.35-11.15c1.3 0 2.55.45 3.55 1.28l-.18.1L11.5 7.7c-.3.17-.48.49-.48.84v7.96l-2.53-1.46V8.46a5.54 5.54 0 0 1 5.53-5.54l-.01.01zM3.68 10.69c.65-1.12 1.66-1.98 2.88-2.43v6.99a.97.97 0 0 0 .48.84l6.88 3.97-2.54 1.47-5.68-3.28a5.54 5.54 0 0 1-2.02-7.56zm1.55 12.83a5.49 5.49 0 0 1-.66-3.7c.04.03.12.07.17.1l5.88 3.4c.3.17.67.17.97 0l6.88-3.98v2.92l-5.69 3.28c-2.65 1.52-6.03.62-7.56-2.02h.01zm11.89 5.08c-1.29 0-2.55-.45-3.54-1.28l.18-.1 5.88-3.39c.3-.17.49-.49.48-.84v-7.95l2.53 1.46v6.57a5.54 5.54 0 0 1-5.53 5.54v-.01zm10.34-7.76a5.52 5.52 0 0 1-2.88 2.42v-6.99c0-.35-.18-.67-.48-.84l-6.89-3.98 2.53-1.46 5.69 3.28a5.53 5.53 0 0 1 2.02 7.56l.01.01z" fill-rule="nonzero"/></g></g></svg>';
		$button->link    = '#';

		return $button;
	}

	/**
	 * Listener for the `onBeforeRender` event.
	 *
	 * @return  void
	 * @since   1.0
	 */
	public function onBeforeRender(): void
	{
		// Check if administrator of site
		if (!$this->app->isClient('administrator') || $this->app->input->get->getString('view') !== 'article') {
			return;
		}

		// Load the Bootstrap modal JS.
		HTMLHelper::_('bootstrap.modal');
	}

	/**
	 * Listener for the `onAfterRender` event.
	 *
	 * @return  void
	 * @since   1.0
	 */
	public function onAfterRender(): void
	{
		// Check if administrator of site
		if (!$this->app->isClient('administrator') || $this->app->input->get->getString('view') !== 'article') {
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

		if ($apiModel == 'text-davinci-003') {
			$tokens = $apitokenHi;
		} else {
			$tokens = $apitokenLow;
		}

		// Get the body text from the Application.
		$content = $this->app->getBody();

		$newBodyOutput = '
		<!-- Modal -->
		<div class="modal fade" id="chatGPTModal" tabindex="-1" aria-labelledby="chatGPTModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title" id="chatGPTModalLabel">'.Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_TITLE').'</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>

					<div class="bg-info text-white small py-2 px-3 d-flex justify-content-end">
						<div>'.Text::_('PLG_EDITORS-XTD_CHATGPT_MODEL').': ' . $apiModel . ' | '.Text::_('PLG_EDITORS-XTD_CHATGPT_TEMP').': ' . $apiTemp . ' | '.Text::_('PLG_EDITORS-XTD_CHATGPT_TOKENS_LOW').': ' . $tokens . '</div>
					</div>
					<div class="modal-body p-3">

						<!--Text area to give input-->
						<div class="mb-3">
							<label for="questionText" class="form-label">'.Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_TITLE').'</label>
							<textarea id="questionText" class="form-control"
								placeholder="'.Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_PH').'"></textarea>
						</div>

						<div class="mb-3 text-center">
							<button id="generateText" class="btn btn-success">'.Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_BTN_GENERATE').'</button>
						</div>

						<div class="asnwer-container position-relative">
							<textarea id="answerText" class="form-control bg-light" placeholder="'.Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_PH_ANSWER').'" readonly></textarea>
						</div>
						<div id="loadingSpin"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_BTN_CLOSE').'</button>
						<button id="r2hbtn" type="button" class="btn btn-primary">'.Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_BTN_INSERT').'</button>
					</div>

				</div>
			</div>
		</div>';

		// Replace the closing body tag with form.
		$buffer = str_ireplace('</body>', $newBodyOutput . '</body>', $content);

		// Output the buffer.
		$this->app->setBody($buffer);
	}

	/**
	 * Triggered before compiling the head.
	 *
	 * @return void
	 */
	public function onBeforeCompileHead(): void
	{
		// Check if administrator of site
		if (!$this->app->isClient('administrator') || $this->app->input->get->getString('view') !== 'article') {
			return;
		}

		/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

		// Load CSS
		$wa->registerAndUseStyle('chatgpt', 'plg_editors-xtd_chatgpt/chatgpt-default.css', [], ['as'=>'style']);
	}
}
