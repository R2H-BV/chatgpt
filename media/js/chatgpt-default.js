/**
 * @copyright	Copyright (c) 2023  R2H BV (https://r2h.nl). All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(() => {
    const options = window.Joomla.getOptions('xtd-chatgpt');

    window.chatgtpPopup = editor => {
        if (!options) {
            // Something went wrong!
            throw new Error('XTD Button ChatGPT not properly initialized');
        }

        const modalElement = document.querySelector('#chatGPTModal');

        if (!modalElement) {
            // Something went wrong!
            throw new Error('XTD Button ChatGPT not properly initialized');
        }

        let apiKey          = options.apikey;
        let apiModel        = options.model;
        let apiTemp         = parseFloat(options.temp);
        let apiTokens       = parseInt(options.tokens);
        let waitingmsg      = options.waitingmsg;
        let errormsg        = options.errormsg;

        let myChatGPTModal  = new bootstrap.Modal(modalElement)

        // selecting dom element
        const textInput = modalElement.querySelector('#questionText');
        const btn       = modalElement.querySelector('#generateText');
        const btnClear  = modalElement.querySelector('#clearText');
        const loader    = modalElement.querySelector('#loadingSpin');
        const answer    = modalElement.querySelector('#answerText');

        // Clear the textarea
        btnClear.addEventListener('click', function () {
            textInput.value = '';
        }, false);

        // showing loading
        function displayLoading() {
            loader.classList.add('display');
            // to stop loading after some time
        }

        // hiding loading
        function hideLoading() {
            loader.classList.remove('display');
        }

        function fetchHandler(event) {
            if (textInput.value.trim() === '') {
                alert(errormsg);
                return;
            }

            answer.value = waitingmsg;
            displayLoading();

            let input = textInput.value;

            let myHeaders = new Headers();
            myHeaders.append('Content-Type', 'application/json');
            myHeaders.append('Authorization', 'Bearer ' + apiKey);

            let raw = JSON.stringify({
                'prompt': input,
                'model': apiModel,
                'max_tokens': apiTokens,
                'temperature': apiTemp
            });

            let requestOptions = {
                method: 'POST',
                headers: myHeaders,
                body: raw,
                redirect: 'follow'
            };

            function gptResponse(chatObj) {
                output = chatObj.choices[0].text;

                // Return the value
                answer.value = output.trim();

                hideLoading();
            }

            fetch('https://api.openai.com/v1/completions', requestOptions)
                .then(response => response.json())
                .then(gptResponse)
                .catch(error => console.log('error', error));
        }

        btn.addEventListener('click', fetchHandler);

        // Clean up all event listeners.
        document.getElementById('chatGPTModal').addEventListener('hide.bs.modal', function (event) {
            document.getElementById('r2hbtn').removeEventListener('click', insertText);
            btn.removeEventListener('click', fetchHandler);
        });

        myChatGPTModal.show();

        const insertText = () => {
            textval = answer.value;

            // Split the array on \n\n
            outputArray = textval.split('\n\n');

            // Remove empty values due to split
            outputArray = outputArray
                .filter(e => !!e)
                .map(content => {
                    const element = document.createElement('p');

                    // Split the content by \n and add a <br> tag.
                    const contentArray = content.split('\n');

                    contentArray.map(text => document.createTextNode(text));

                    contentArray.forEach((text, index) => {
                        if (index > 0) {
                            element.appendChild(document.createElement('br'));
                        }

                        element.appendChild(document.createTextNode(text));
                    });

                    return element;
                });

            // Join the array back together as elememnts.
            const template = document.createElement('template');
            outputArray.forEach(e => template.content.appendChild(e));

            textval = template.innerHTML;

            Joomla.editors.instances[editor].replaceSelection(textval);

            myChatGPTModal.hide();
        };

        // adding event listener to button
        document.getElementById('r2hbtn').addEventListener('click', insertText)

        return true;
    };
})();
