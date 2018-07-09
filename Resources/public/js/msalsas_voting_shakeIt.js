// This file is part of the MsalsasVotingBundle package.
//
// (c) Manolo Salsas
//
//     For the full copyright and license information, please view the LICENSE
//     file that was distributed with this source code.

(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var shakeItLink = document.querySelectorAll('.msalsas-voting-shake-it a');
        for (var i = 0; i < shakeItLink.length; i++) {
            if (shakeItLink[i].addEventListener) {
                shakeItLink[i].addEventListener('click', shakeIt, false);
            } else {
                shakeItLink[i].attachEvent('onclick', shakeIt);
            }
        }
    });

    function shakeIt(evt) {
        var shakeItButton = evt.target.parentNode;
        var id = shakeItButton.dataset.id;
        var url = shakeItButton.dataset.url;
        var shakenText = shakeItButton.dataset.shakentext;
        var http = new XMLHttpRequest();
        http.open('POST', url, true);
        http.setRequestHeader('Content-type', 'application/json');
        http.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        http.onreadystatechange = function() {
            if(http.readyState == 4 && http.status == 200) {
                var shakesElem = document.getElementById('msalsas-voting-shakes-' + id);
                shakesElem.text = document.createTextNode(http.responseText).wholeText;
                var buttonElem = document.getElementById('msalsas-voting-a-shake-' + id);
                buttonElem.innerHTML = '<span>' + shakenText + '</span>';
            } else if(http.readyState == 4 && http.status >= 400) {
                alert(http.responseText);
            }
        };
        http.send();
    }
})();