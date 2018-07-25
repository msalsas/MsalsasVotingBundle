Getting Started With MsalsasVotingBundle
========================================

This bundle provides a way for voting posts or whatever.

Prerequisites
-------------

This version of the bundle requires Symfony 4.0+.

Translations
~~~~~~~~~~~~

If you wish to use default texts provided in this bundle, you have to make
sure you have translator enabled in your config.

.. code-block:: yaml

    # config/packages/translation.yaml

    framework:
        translator: ~

For more information about translations, check `Symfony documentation`_.

.. _Symfony documentation: https://symfony.com/doc/current/book/translation.html


Installation
------------

Installation is a quick 7 step process (4 if you use Flex):

1. Download MsalsasVotingBundle using composer
2. Enable the Bundle
3. Create your Reference class (post, article or whatever)
4. Configure the MsalsasVotingBundle
5. Create MsalsasVotingBundle routing
6. Update your database schema
7. Display the widgets


Step 1: Download MsalsasVotingBundle using composer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Require the bundle with composer:

.. code-block:: bash

    $ composer require msalsas/voting-bundle

Composer will install the bundle to your project's ``vendor/msalsas/voting-bundle`` directory.


Step 2: Enable the bundle
~~~~~~~~~~~~~~~~~~~~~~~~~

.. note::

    If you're using Flex, this is done automatically

Enable the bundle in the kernel:

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Msalsas\VotingBundle\MsalsasVotingBundle(),
            // ...
        );
    }


Step 3: Create your Reference class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The goal of this bundle is to handle votes and clicks for a ``Reference``
(or ``Post``, ``Article`` or whatever) class and persist them to a database (MySql).
Your first job, then, is to create the ``Reference`` class
for your application. This class can look and act however you want: add any
properties or methods you find useful. This is *your* ``Reference`` class.


Step 4: Configure the MsalsasVotingBundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. note::

    If you're using Flex, this is done automatically

Add the following configuration to your ``config/packages/msalsas_voting.yaml`` file.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/msalsas_voting.yaml
        msalsas_voting:
            user_provider: \App\Entity\User # Your ``User`` class
            negative_reasons: # You can override the default reasons with your translation references
                - msalsas_voting.negative_reasons.irrelevant
                - msalsas_voting.negative_reasons.old
                - msalsas_voting.negative_reasons.tiredness
                - msalsas_voting.negative_reasons.sensationalist
                - msalsas_voting.negative_reasons.spam
                - msalsas_voting.negative_reasons.duplicated
                - msalsas_voting.negative_reasons.microblogging
                - msalsas_voting.negative_reasons.erroneous
                - msalsas_voting.negative_reasons.plagiarism


Step 5: Create MsalsasVotingBundle routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. note::

    If you're using Flex, this is done automatically

Now that you have activated and configured the bundle, you have to create the routing file.
You can edit the paths to whatever you want, but including ``{id}``.

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


Step 6: Update your database schema
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that the bundle is configured, the last thing you need to do is update your
database schema.

Run the following command.

.. code-block:: bash

    $ php bin/console doctrine:schema:update --force


Step 7: Display the widgets
~~~~~~~~~~~~~~~~~~~~~~~~~~~

For displaying the voting widgets you have to use macros:

.. code-block:: html+jinja

    {% import "@msalsas_voting/msalsas_voting_widget.html.twig" as msalsas_voting_widget %} # Import macros
    {{ msalsas_voting_widget.shakeItCSS() }} # Import CSS macro (optional)
    {{ msalsas_voting_widget.shakeItJS() }} # Import JS macro
    {{ msalsas_voting_widget.shakeItModal() }} # Import Modal macro (for errors)

    <article class="post">
        {{ msalsas_voting_widget.shakeIt(post.id) }} # Import the voting widget
        <h2>
            <a href="{{ path('blog_post', {slug: post.slug}) }}"> # This is just an example
                {{ post.title }}
            </a>
        </h2>

        ...

        {{ msalsas_voting_widget.bottomBar(post.id) }} # Import bottom bar widget (includes negative voting form)
    </article>

Also, you have to import `Font Awesome`_ if you want to show the bottom bar icons


Instead of using

``msalsas_voting_widget.shakeItCSS()``

``msalsas_voting_widget.shakeItJS()``

you can import

``vendor/msalsas/voting-bundle/Resources/public/css/msalsas_voting_styles.css``

``vendor/msalsas/voting-bundle/Resources/public/js/msalsas_voting_shakeIt.js``

with your assets.

If you don't use ``shakeItModal`` errors will be displayed with ``alert``.

.. _Font Awesome: https://fontawesome.com/how-to-use/on-the-web/setup/getting-started?using=web-fonts-with-css


Next Steps
~~~~~~~~~~

Now that you have completed the basic installation and configuration of the
MsalsasVotingBundle, you are ready to learn about more advanced features and usages
of the bundle.

The following documents are available:

`1. Clicks or views`_.

`2. Publishing`_.

`3. Configuration reference`_.

.. _1. Clicks or views: ./clicks_or_views.rst
.. _2. Publishing: ./publish.rst
.. _3. Configuration reference: ./configuration_reference.rst

