# Generating the API Token & Fixtures

The most important property on `ApiToken` is the token string... which needs to
be something random. Create a construct method with a `string $tokenType` argument:

[[[ code('8783e12576') ]]]

This isn't mandatory, but GitHub has caught onto something neat - since
they have different types of tokens, like personal access tokens and OAuth tokens -
they give each token type its own prefix. It just helps figure out where each
comes from.

We're only going to have one type, but we'll follow the idea. On top, to
store the type prefix, add `private const PERSONAL_ACCESS_TOKEN_PREFIX = 'tcp_'`:

[[[ code('776bfd9e9b') ]]]

I... just made up that prefix. Our site is called Treasure Connect... and this is
a personal access token, so `tcp_`.

Below, for `string $tokenType =` default it to
`self::PERSONAL_ACCESS_TOKEN_PREFIX`:

[[[ code('34ca0ceeaf') ]]]

For the token itself, say `$this->token = $tokenType.` and then I'll use some code
that will generate a random string that's 64 characters long:

[[[ code('3e72ca8311') ]]]

So that's 64 characters here plus the 4 character prefix equals 68. That's why
I chose that length. And because we're setting the `$token` in the constructor,
this doesn't need to `= null` or be nullable anymore. It will always be a `string`.

## Setting up the Fixtures

Ok! This is set up! So let's add some API tokens to the database. At your
terminal, run

```terminal
php ./bin/console make:factory
```

so we can generate a Foundry factory for `ApiToken`. Go check out the new class:
`src/Factory/ApiTokenFactory.php`. Down in `getDefaults()`:

[[[ code('88d2e1bb97') ]]]

This looks mostly fine, though we don't need to pass in the `token`. Oh, and I
want to tweak the scopes:

[[[ code('1b34bbdd8a') ]]]

Typically, when you create an access token - whether it's a personal access
token or one created through OAuth - you're able to *choose* which permissions
that token will have: it does *not* automatically have *all* the permissions that
a normal user would. I want to add *that* into our system as well.

Back over in `ApiToken`, at the top, after the first constant, I'll paste in a
few more:

[[[ code('0433e894db') ]]]

This defines three different scopes that a token can have. This isn't all the scopes
we could imagine, but it's enough to make things realistic. So, when you create
a token, you can choose whether that token should have permission to edit user data,
or whether it can create treasures on behalf of the user or whether it can edit
treasures on behalf of the user. I also added a `public const SCOPES` to describes
them:

[[[ code('ce1ba1730e') ]]]

Back over in our `ApiTokenFactory`, let's, by default, give each `ApiToken` two
of those three scopes:

[[[ code('641102420a') ]]]

Ok! `ApiTokenFactory` is ready. Last step: open `AppFixtures` so we can *create*
some `ApiToken` fixtures. I want to make sure that, in our dummy
data, each user has at least one or two API tokens. An easy way to do that, down
here is to say `ApiTokenFactory::createMany()`. Since we have 10 users, let's create
30 tokens. Then pass that a callback function and, inside, return an override for
the default data. We're going to override the `ownedBy` to be `UserFactory::random()`:

[[[ code('90c32c2c0c') ]]]

So this will create 30 tokens and assign them randomly to the 10, well really
11, users in the database. So on average, each user should have about three API tokens
assigned to them. I'm doing this because, to keep life simple, we're *not*
going to build a user interface where the user can *actually* click and create
access tokens and select scopes. We're going to skip all that. Instead, since
every user will already have some API tokens in the database, we can jump straight
to learning how to read and *validate* those tokens.

Reload the fixtures with:

```terminal
symfony console doctrine:fixtures:load
```

## Showing the Tokens on the Frontend

And... beautiful! But since we're *not* going to build an interface for creating
tokens, we at least need an easy way to *see* the tokens for a user... so we can
test them in our API. When we're authenticated, we can show them right here.

This isn't a very important detail, so I'll do it real quick. Over in `User`,
at the bottom, I'll paste in a function that returns an array of the valid API
token strings for this user:

[[[ code('74f02bde2d') ]]]

In `ApiToken`, we also need an `isValid()` method... so I'll paste that as well:

[[[ code('7d53689fdd') ]]]

You can get all of this from the code blocks on this page.

Next, open up `assets/vue/controllers/TreasureConnectApp.vue`... and add a new
prop that can be passed in: `tokens`:

[[[ code('78239d1a12') ]]]

Thanks to that, we'll have a new `tokens` variable in the template. After the
"Log Out" link, I'll paste in some code that renders those:

[[[ code('c50f9a4cf7') ]]]

Last step: open `templates/main/homepage.html.twig`. This is where we're passing
props to our Vue app. Pass a new one called `tokens` set to, if `app.user`, then
`app.user.validTokenStrings`, else `null`:

[[[ code('af5d685f49') ]]]

Let's try this! If we refresh, right now we are not logged in. Use our cheater
links to log in. Notice that it doesn't show them immediately... we could improve
our code to do that... but it's not a big deal. Refresh and... there they are!
We have *two* tokens!

Next: let's write a system so that can *read* these tokens and authenticate the
user instead of using session authentication.
