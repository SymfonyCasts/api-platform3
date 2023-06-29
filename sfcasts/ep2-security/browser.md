# JSON Test Assertions & Seeding the Database

Let's make this test real with data and assertions.

There are two main ways to do assertions with Browser. First, it comes with a bunch
of built-in methods to help, like `->assertJson()`. Or... you can always just grab
the JSON that comes back from an endpoint and check things using the built-in
PHPUnit assertions you know and love. We'll see both.

Let's start by checking `->assertJson()`:

[[[ code('6d27846281') ]]]

When we run that:

```terminal-silent
symfony php bin/phpunit
```

It passes! Cool! We know that this response should  have a `hydra:totalItems`
property set to the number of results. Right now, our database is empty... but we
can at least assert that it matches zero.

To do that, use `->assertJsonMatches()`.

This is a special method from Browser that uses a special syntax that allows
us to read different parts off the JSON. We'll dig into it in a minute.

But this one is simple: assert that `hydra:totalItems` equals `0`:

[[[ code('732b364ada') ]]]

When we try this:

```terminal-silent
symfony php bin/phpunit
```

It fails! But with a great error:

> `mtdowling/jmespath.php` is required to search JSON

## Hello JMESPath

Ah, we need to install that! Copy the `composer require` line, find your terminal,
and run it:

```terminal-silent
composer require mtdowling/jmespath.php --dev
```

This "JMESPath" thing is actually super cool: it's a "query language" for reading
different parts of any JSON. For example, if this is your JSON and you want to
read the `a` key, just say `a`. Simple.

But you can also do deeper, like: `a.b.c.d`. Or, get crazier: grab the `1`
index, or grab `a.b.c`, then the `0` index, `.d`, the `1` index then the `0`
index. You can even slice the array in different ways. Basically... you can go
nuts.

But we're *not* going to lose our minds with this. It's a handy syntax... but if
things  get too complex, we can always test the JSON manually, which we'll do in
a bit.

Anyway, now that we have the library installed, let's run the test again.

```terminal-silent
symfony php bin/phpunit
```

It still fails! With a weird error:

> Syntax error at character 5 `hydra:totalItems`.

Unfortunately, the `:` is a special character inside of JMESPath. So
whenever we have a `:`, we need to put quotes around that key:

[[[ code('a1e5a46dba') ]]]

Not ideal, but not a huge inconvenience.

Now when we try it:

```terminal-silent
symfony php bin/phpunit
```

It passes!

## Seeding the Database

But... this isn't a very interesting test: we're just asserting that we get nothing
back... because the database is empty. To make our test *real*, we need data: we
need to *seed* the database with data at the start of the test.

Fortunately, Foundry makes that dead-simple. At the top, call
`DragonTreasureFactory::createMany()` and let's create 5 treasures. Now, below,
assert that we get 5 results:

[[[ code('2ce69e5a6f') ]]]

It's just that simple. And actually, let me put our dump back so we can see
the result:

[[[ code('d6551be2e2') ]]]

Try it now:

```terminal-silent
symfony php bin/phpunit
```

It passes! And if you look up, yea! The response has 5 treasures! Dang, that was
easy.

Next: let's use JMESPath to assert something more challenging. Then we'll back up
and see how we can dig into Browser to give us infinite flexibility - and simplicity -
when it comes to testing JSON.
