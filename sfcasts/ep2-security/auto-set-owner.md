# Auto Setting the "owner"

Every `DragonTreasure` must have an `owner`... and to set that, when you `POST`
to create a treasure, we *require* that field. I think we should make that
optional. So, in the test, *stop* sending the `owner` field:

[[[ code('9940b47918') ]]]

When this happens, let's automatically set it to the currently-authenticated user.

Make sure the test fails. Copy the method name... and run it:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Nailed it. Got a 422, 201 expected. That 422 is a validation error from the `owner`
property: this value should not be null.

## Removing the Owner Validation

If we're going to make it optional, we need to remove that `Assert\NotNull`:

[[[ code('b2c1c12e9c') ]]]

And now when we try the test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Well hello there gorgeous 500 error! Probably it's because the null `owner_id` is
going kaboom when it hits the database. Yup!

## Using the State Processors

So: how can we automatically set this field when it's not sent? In the previous
API Platform 2 tutorial, I did this with an entity listener, which is a fine solution.
But in API Platform 3, just like when we hashed the user password, there's now a
really nice system for this: the state processor system.

As a reminder, our POST and PATCH endpoints for `DragonTreasure` already have a state
processor that comes from Doctrine: it's responsible for saving the object to
the database. Our goal will feel familiar at this point: to *decorate* that state
process so we can run extra code before saving.

Like before, start by running:

```terminal
php bin/console make:state-processor
```

Call it `DragonTreasureSetOwnerProcessor`:

[[[ code('fdf8a18e84') ]]]

Over in `src/State/`, open that up. Ok, let's decorate! Add the construct method
with `private ProcessorInterface $innerProcessor`:

[[[ code('b66e1324b2') ]]]

Then down in `process()`, call that! This method doesn't return anything - it has
a `void` return - so we just need `$this->innerProcessor` - don't forget that
part like I am - `->process()` passing `$data`, `$operation`, `$uriVariables` and
`$context`:

[[[ code('63f6998dee') ]]]

Now, to make Symfony *use* our state processor instead of the *normal* one from
Doctrine, add `#[AsDecorator]`... and the id of the service is
`api_platform.doctrine.orm.state.persist_processor`:

[[[ code('08ff7fd147') ]]]

Cool! Now, everything that uses that service in the system will be passed *our*
service instead... and then the original will be passed into us.

## Decorating Multiple Times is Ok!

Oh, and there's something cool going on. Look at `UserHashPasswordStateProcessor`.
We're decorating the *same* thing there! Yea, we're decorating that service
*twice*, which is totally allowed! Internally, this will create a, sort
of, *chain* of decorated services.

Ok, let's get to work setting the owner. Autowire our favorite `Security` service
so we can figure out who is logged in:

[[[ code('b183c1fa93') ]]]

Then, before we do the saving, if `$data` is an `instanceof DragonTreasure`
and `$data->getOwner()` is null *and* `$this->security->getUser()` - making
sure the user is logged in - then `$data->setOwner($this->security->getUser())`:

[[[ code('338882358b') ]]]

That should do it! Run that test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Yikes! Allowed memory size exhausted. I smell recursion! Because... I'm calling
`process()` on myself: I need `$this->innerProcessor->process()`:

[[[ code('fcbf2eb93f') ]]]

Now:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

A passing test is *so* much cooler than recursion. And the owner field *is* now
optional!

Next: we currently return *all* treasures from our GET collection endpoint,
including *unpublished* treasures. Let's fix that by modifying the query behind
that endpoint to hide them.
