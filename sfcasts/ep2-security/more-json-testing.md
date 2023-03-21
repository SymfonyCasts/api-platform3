# Advanced & Flexible JSON Test Assertions

We might also want to test that we get the correct fields in the response for
each item. Can we do that with JMESPath? Sure! The `assertJsonMatches()` method
is really handy. And actually, if you hold command or control and click into it,
when we call `assertJsonMatches()`, behind the scenes, it calls
`$this->json()`. This creates a `Json` object... which has even *more* useful methods.
The `Browser` instance itself gives us access to `assertJsonMatches()`. But if we
want to use any of its other methods, we need to do a bit more work.

The first way to use the `Json` object is via Browser's `use()` method. Pass this
a callback with a `Json $json` argument.

This is a magic feature of browser: it reads the type-hint of the argument, and
knows to pass us the `Json` object. You could also type-hint a `CookieJar` object,
`Crawler` or a few other things.

The point is: because we type-hinted the argument with `Json`, it will grab the
`Json` object for the last response and pass it to us. Let's use it to do some
experimenting. We want to check what the *keys* are for the first item
inside of `hydra:member`. To help figure the expression we need, let's use a method
called `search()`. This allows us to use a `JMESPath` expression and get back the
result. Do double quotes then `hydra:member` to see what it returns. And... remove
the other dump.

Ok! Run that test again:

```terminal-silent
symfony php bin/phpunit
```

It passes... but more importantly, look at the dump! It's the array of 5 items.
Ok... let's grab the `0` index. After the `hydra:member` double quotes, add
`[0]`. Then surround the *entire* thing with a `keys()` function from JMESPath.

Try that now.

```terminal-silent
symfony php bin/phpunit
```

Oh that's lovely. And it's probably one of the more complex things that you'll do.
Now that we've got the path right, turn that into an assertion. You can do that
by setting this to a variable - like `$keys` - and using a normal assertion. Or
you can change `search` to `assertMatches()` and pass a second argument: the array
of the expected fields.

We should be good! Try it:

```terminal-silent
symfony php bin/phpunit
```

It passes! And yes, we *could* now remove the `use()` method and move this to
a normal `->assertJsonMatches()` call.

## Doing Normal JSON Assertions

As cool as this JMESPath stuff is, it *is* another thing to learn and it *can*
get complex. So what's the alternative?

Assign the entire `$browser` chain to a new `$json` variable and then add `->json()`
to the end. *Most* methods on `Browser` return... a `Browser`, which let's us do
all the fun chaining. But a few, like `->json()` let us "break out" of browser
so we can do something  custom.

This allows us to remove the `use()` function here and replace the assertions
with more traditional PHPUnit code. We could *still* use the `Json` object directly...
that passes... or to remove all fanciness, change to `$this->assertSame()`
that `$json->decoded()['hydra:member'][0]` - `array_keys()` around everything - matches
our array. And of course... that passes to!

So, a lot of power... but also a lot of flexibility to write tests how you want.

Next, let's add tests for authentication: both logging in via our login form and
via an API token.
