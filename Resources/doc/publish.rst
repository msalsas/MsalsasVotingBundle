MsalsasVotingBundle publishing
==============================

For displaying the published style for a reference you have to set it to published from a controller
type-hinting the ``Voter`` and calling ``setPublished``:


.. code-block:: php

    <?php

    public function postClick($postId, Voter $voter): Response
    {
        $voter->setPublished($post->getId());

        // ...
    }


You can also use ``isPublished`` method.

Let's say you want to show all published posts in a page. You could use the repository to find them:

.. code-block:: php

    <?php

    $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findBy(array('published' => true));


``$referenceVotes`` will be an array of ``ReferenceVotes``, and you can get the reference id with:


.. code-block:: php

    <?php

    $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findBy(array('published' => true));
    foreach ($referenceVotes as $referenceVote) {
        $id = $referenceVote->getReference();
    }