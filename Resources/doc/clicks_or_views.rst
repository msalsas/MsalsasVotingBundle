MsalsasVotingBundle clicks or views
===================================

For incrementing the clicks or views of a reference you have to type-hint the ``Clicker`` in a
controller action and call ``addClick`` with the reference id (you will have to configure this route):


.. code-block:: php

    <?php

    public function postClick($postId, Clicker $clicker): Response
    {
        $clicker->addClick($post->getId());

        // ...
    }

The ``addClick`` method will check if the user has already clicked. If it is an anonymous
user, it will check for the client IP.

If you want to make use of views instead of clicks, just override the clicks translation
and call ``$clicker->addClick($post->getId())`` on the post view controller action.


.. code-block:: php

    <?php

    public function postShow($postId, Clicker $clicker): Response
    {
        $clicker->addClick($post->getId());

        // ...
    }