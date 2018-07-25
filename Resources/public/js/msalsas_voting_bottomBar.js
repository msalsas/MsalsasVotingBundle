// This file is part of the MsalsasVotingBundle package.
//
// (c) Manolo Salsas
//
//     For the full copyright and license information, please view the LICENSE
//     file that was distributed with this source code.

(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var voteNegativeForm = document.querySelectorAll('.msalsas-voting-bottom-bar form');
        for (var i = 0; i < voteNegativeForm.length; i++) {
            if (voteNegativeForm[i].addEventListener) {
                voteNegativeForm[i].addEventListener('change', voteNegative, false);
            } else {
                voteNegativeForm[i].attachEvent('onchange', voteNegative);
            }
        }
    });

    function voteNegative(evt) {
        var form = evt.target.parentNode;
        var id = form.dataset.id;
        var options =  form.ratings.options;
        window.msalsasVoting_Selected = options[form.ratings.selectedIndex];
        var elem = document.getElementById('msalsas-voting-problem-' + id);
        var url = elem.dataset.url;
        var http = new XMLHttpRequest();
        http.open('POST', url, true);
        http.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        http.onreadystatechange = function() {
            if(http.readyState == 4 && http.status == 200) {
                var downVotes = document.getElementById('msalsas-voting-bottom-bar-votes-down-' + id);
                downVotes.innerHTML = document.createTextNode(http.responseText).wholeText;
                var buttonElem = document.getElementById('msalsas-voting-a-shake-' + id);
                buttonElem.innerHTML = '<span>' + window.msalsasVoting_Selected.text + '</span>';
            } else if(http.readyState == 4 && http.status >= 400) {
                if (http.responseText.length < 50) {
                    showModal(http.responseText);
                } else {
                    showModal('Error');
                }
            }
        };
        if (window.msalsasVoting_Selected.value !== '0') {
            http.send(window.msalsasVoting_Selected.value);
        }
    }

    function showModal(message) {
        message = message.replace (/(^")|("$)/g, '');
        var modal = document.getElementById('msalsas-modal');
        var span = document.getElementsByClassName("msalsas-close")[0];

        if (!modal || !span) {
            alert(message);
            return;
        }
        document.getElementById('msalsas-modal-text').innerText = message;
        modal.style.display = "block";

        if (span.addEventListener) {
            span.addEventListener('click', closeModal, false);
        } else {
            span.attachEvent('onclick', closeModal);
        }

        if (window.addEventListener) {
            window.addEventListener('click', closeModal, false);
        } else {
            window.attachEvent('onclick', closeModal);
        }
    }

    function closeModal(event) {
        var modal = document.getElementById('msalsas-modal');
        var span = document.getElementsByClassName("msalsas-close")[0];
        if (event.target === modal || event.target === span) {
            modal.style.display = "none";
        }
    }
})();