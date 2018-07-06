MsalsasVotingBundle displaying widgets
======================================

For displaying the voting widgets you have to use macros:


.. code-block:: html+jinja

    {% import "@msalsas_voting/msalsas_voting_widget.html.twig" as msalsas_voting_widget %} # Import macros
    {{ msalsas_voting_widget.shackeItCSS() }} # Import CSS macro (optional)
    {{ msalsas_voting_widget.shackeItJS() }} # Import JS macro (required)

    <article class="post">
        {{ msalsas_voting_widget.shackeIt(post.id) }} # Import the voting widget
        <h2>
            <a href="{{ path('blog_post', {slug: post.slug}) }}"> # This is just an example
                {{ post.title }}
            </a>
        </h2>

        ...

        {{ msalsas_voting_widget.bottomBar(post.id) }} # Import bottom bar widget (includes negative voting form)
    </article>

