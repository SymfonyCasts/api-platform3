# Bootstrapping a Killer Test System

Our API is getting more and more complex. And doing *manually* testing is *not* a
great long-term plan. So let's install some tools to get a killer test setup.

## Installing the test-pack

Step one: at your terminal run:

```terminal
composer require test
```

This is a flex alias for a package called `symfony/test-pack`. Remember: packs are
shortcut packages that actually install a bunch of *other* packages. For example,
when this finishes... and we check out `composer.json`, you can see down in
`require-dev` that this added PHPUnit itself as well as a few other tools from
Symfony  to help testing:

[[[ code('58168dd412') ]]]

It also executed a recipe which added a number of files. We have `phpunit.xml.dist`,
a `tests/` directory, `.env.test` for test-specific environment variables and
even a little `bin/phpunit` executable shortcut that we'll use to run our tests.

## Hello browser Library

No surprise, Symfony has tools for testing and these can be used to
test an API. Heck, API Platform even has their *own* tools built on *top* of those
to make testing an API even easier. And yet, I'm going to be stubborn
and use a totally *different* tool that I've fallen in love with.

It's called [Browser](https://github.com/zenstruck/browser), and it's *also*
built on top of Symfony's testing tools: almost like a nicer interface above
that strong base. It's just... super fun to use. Browser gives us a fluid interface
that can be used for testing web apps, like you see here, or testing APIs.
It can also can be used to test pages that use JavaScript.

Let's get this guy installed. Copy the `composer require` line, spin back over and
run that:

```terminal-silent
composer require zenstruck/browser --dev
```

While that's doing its thing, it's optional, but there's an "extension" that you
can add to `phpunit.xml.dist`. Add it down here on the bottom:

[[[ code('21ca5680f1') ]]]

In the future, if you're using PHPUnit 10, this will likely be replaced by
some `listener` config.

This adds a few extra features to browser. Like, when a test fails, it
will automatically save the last response to a file. We'll see this soon. And if
you're using JavaScript testing, it'll take screenshots of failures!

## Creating our First Test

Ok, we're ready for our first test. In the `tests/` directory, it doesn't
matter how you organize things, but I'm going to create a `Functional/`
directory because we're going to be making functional tests to our API. Yup,
we'll literally create an API client, make GET or POST requests and then assert
that we get back the correct output.

Create a new class called `DragonTreasureResourceTest`. A normal test extends
`TestCase` from PHPUnit. But make this extend `KernelTestCase`: a class from
Symfony that extends `TestCase`... but gives us access to Symfony's engine:

[[[ code('a88e674d33') ]]]

Let's start by testing the GET collection endpoint to make sure we get back
the data we expect. To activate the browser library, at the top, add a trait
with `use HasBrowser`:

[[[ code('c8e8a8248b') ]]]

Next, add a new test method: `public function`, how about
`testGetCollectionOfTreasures()`... which will return `void`:

[[[ code('8939576a8f') ]]]

Using browser is dead simple thanks to that trait: `$this->browser()`. Now we
can make GET, POST, PATCH or whatever request we want. Make a GET request
to `/api/treasures` and then, just to see what that looks like, use this nifty
`->dump()` function:

[[[ code('e2226bdf2a') ]]]

## Running our Tests through the symfony Binary

How cool is that? Let's see what it looks like. To execute our test, we could run:

```terminal
php ./vendor/bin/phpunit
```

That works just fine. But one of the recipes also added a shortcut file:

```terminal
php bin/phpunit
```

When we run that, ooh, let's see. The `dump()` *did* happen: it dumped out the
response... which was some sort of error. It says:

> SQLSTATE: connection to server port 5432 failed.

Hmm, it can't connect to our database. Our database is running via a Docker
container... and then, because we're using the `symfony` web server, when we use
the site via a browser, the `symfony` web server detects the Docker container and
sets the `DATABASE_URL` environment variable *for* us. That's how our API has been
able to talk to the Docker database.

When we've run *commands* that need to talk to the database, we've been running
them like `symfony console make:migration`... because when we execute things through
`symfony`, it adds the `DATABASE_URL` environment variable... and *then* runs the
command.

So, when we simply run `php bin/phpunit`... the real `DATABASE_URL` is missing.
To fix that, run:

```terminal
symfony php bin/phpunit
```

It's the same thing... except it lets `symfony` add the `DATABASE_URL`
environment variable. And now... we see the dump again! Scroll to the top. Better!
Now the error says:

> Database `app_test` does not exist.

## Test-Specific Database

Interesting. To understand what's happening, open `config/packages/doctrine.yaml`.
Scroll down to a `when@test` section. This is cool: when we're in the `test`
environment, there's a bit of config called `dbname_suffix`. Thanks to this, Doctrine
will take our *normal* database name and add `_test` to it:

[[[ code('0e0c04410b') ]]]

This next part is specific to a library called ParaTest where you can run tests
in parallel. Since we're not using that, it's just an empty string and not something
we need to worry about.

Anyway, that's how we end up with an `_test` at the end of our database name. And
we want that! We don't want our `dev` and `test` environments to use the same database
because it gets annoying when they run over each other's data.

By the way, if you're *not* using the `symfony` Binary and Docker
setup... and you're configuring your database manually, be aware that in the `test`
environment, the `.env.local` file is *not* read:

[[[ code('70831f3973') ]]]

The `test` environment is special: it skips reading `.env.local` and only
reads `.env.test`. You can also create a `.env.test.local` for env vars
that are read in the `test` environment but that won't be committed to
your repository.

## The ResetDatabaseTrait

Ok, in the `test` environment, we're missing the database. We could easily fix this
by running:

```terminal
symfony console doctrine:database:create --env=test
```

But that's *way* too much work. Instead, add one more trait to our test class:
`use ResetDatabase`:

[[[ code('0ff7a75753') ]]]

This comes from Foundry: the library we've been using to create dummy fixtures
via the factory classes. `ResetDatabase` is *amazing*. It automatically makes sure
that the database is *cleared* before each test. So if you have two tests, your
second test isn't going to mess up because of some data that the first test added.

It's also going to create the database automatically for us. Check it out. Run

```terminal
symfony php bin/phpunit
```

again and check out the dump. That's our response! It's our beautiful JSON-LD! We
don't have any *items* in the collection yet, but it *is* working.

And notice that, when we make this request, we are *not* sending an `Accept`
header on the request. Remember, when we use the Swagger UI... it actually *does*
send an `Accept` header that advertises that we want `application/ld+json`.

We *can* add that to our test if we want. But if we pass nothing, we get JSON-LD
back because that's the *default* format of our API.

Next: let's properly finish this test, including seeding the database with data
and learning about Browser's API assertions.
