<?php
declare(strict_types = 1);

use Joomla\CMS\Language\Text;

?>
<!-- Modal -->
<div class="modal fade" id="chatGPTModal" tabindex="-1" aria-labelledby="chatGPTModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="chatGPTModalLabel">
                    <?php echo Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_TITLE'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="bg-info text-white small py-2 px-3 d-flex justify-content-end">
                <div>
                    <?php echo Text::_('PLG_EDITORS-XTD_CHATGPT_MODEL'); ?>:
                    <?php echo $apiModel; ?> |
                    <?php Text::_('PLG_EDITORS-XTD_CHATGPT_TEMP'); ?>:
                    <?php echo $apiTemp; ?> |
                    <?php echo Text::_('PLG_EDITORS-XTD_CHATGPT_TOKENS_LOW'); ?>:
                    <?php echo $tokens; ?>
                </div>
            </div>
            <div class="modal-body p-3">
                <!--Text area to give input-->
                <div class="mb-3">
                    <label for="questionText" class="form-label">
                        <?php echo Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_TITLE'); ?>
                    </label>
                    <textarea id="questionText" class="form-control"
                        placeholder="'.Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_PH').'"></textarea>
                </div>

                <div class="mb-3 text-center">
                    <button id="generateText" class="btn btn-success">
                        <?php echo Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_BTN_GENERATE'); ?>
                    </button>
                    <button id="clearText" class="btn btn btn-secondary">
                        <?php echo Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_BTN_CLEAR'); ?>
                    </button>
                </div>

                <div class="asnwer-container position-relative">
                    <textarea
                        id="answerText"
                        class="form-control bg-light"
                        placeholder="<?php echo Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_PH_ANSWER'); ?>"
                        readonly></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php echo Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_BTN_CLOSE'); ?>
                </button>
                <button id="r2hbtn" type="button" class="btn btn-primary">
                    <?php echo Text::_('PLG_EDITORS-XTD_CHATGPT_MODAL_QUESTION_BTN_INSERT'); ?>
                </button>
            </div>
            <div id="loadingSpin">
                <div id="loadingSpinCircle"></div>
            </div>
        </div>
    </div>
</div>
