Routing configuration
=====================

By default, the routing file ``@MsalsasVotingBundle/Resources/config/routes.xml`` enables all the routes.
In the case you want to enable, disable or modify the different available routes, just use the routing
configuration file.

.. configuration-block::

    .. code-block:: yaml

        # config/routes/msalsas_voting.yml
        positive_vote:
            path: /vote-positive/{_locale}/{id}
            controller: Msalsas\VotingBundle\Controller\VoteController:votePositive
            methods: POST
        negative_vote:
            path: /vote-negative/{_locale}/{id}
            controller: Msalsas\VotingBundle\Controller\VoteController:voteNegative
            methods: POST
