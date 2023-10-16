# Triggering a "Publish"

We're down to just one test failure... it's in `testPublishTreasure`. Let's check
it out. Ok, this tests to make sure that a notification is created in the database
when the status of a treasure changes from `'isPublished' => false` to `'isPublished'
=> true`. Previously, we implemented this via a custom state processor.

But now, we *could* put this into the mapping! In `DragonTreasureApiToEntityMapper`,
we could check to see if the entity *was* `'isPublished' => false` and is now
*changing* to `'isPublished' => true`. If it *is*, create a notification right
there. And if this sounds good to you, go for it!

However, for me, putting the logic here doesn't *quite* feel like the right place...
just because it's a "data mapper", so it feels weird to do something *beyond* just
mapping the data.

## Creating the State Processor

*So*, let's to back to our original solution: creating a *state processor*. Over
at you terminal, run:

```terminal
php bin/console make:state-processor
```

And call it `DragonTreasureStateProcessor`. Our goal should feel familiar: we'll
add some custom logic here, but call the *normal* state processor to let it do
the heavy lifting.

To do that, add a `__construct()` method with
`private EntityClassDtoStateProcessor $innerProcessor`. Down here, use that with
`return $this->innerProcessor->process()` passing the arguments it needs: `$data`,
`$operation`, `$uriVariables`, and `$context`. Ah, and you can see that this is
highlighted in red. This isn't really a `void` method, so remove that.

Ok, let's hook our API resource to use this! Inside `DragonTreasureApi`, change
the processor to `DragonTreasureStateProcessor`.

At this point, we haven't really changed anything: the system will call our new
processor... which just calls the *old* one. And so when we rerun the tests:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

## Detecting the isPublished Change

Everything still works except for that last failure. So let's add our notification
code! Earlier, we figured out if `isPublished` was changing from `false` to
`true` by using the "previous data" that's inside of the `$context`. Dump
`$context['previous_data']` to see what that looks like.

Now, run *just* this test:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPublishTreasure
```

Cool! We see that previous data is the `DragonTreasureApi` with `isPublished: false`,
because that's the value our entity starts with in the test. Let's also dump `$data`.

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPublishTreasure
```

Okay, the original one has `isPublished: false`, and the *new* one has
`isPublished: true`! And *that* makes us dangerous.

Back over, we already wrote the notification code before... so I'll just paste it
back in. This is delightfully boring code! We use` $previousData` and `$data` to
detect the state change from `isPublished` false to true... then create a
`Notification`.

The only thing that's *kind of* interesting part is that the `Notification` entityis
related to a `DragonTreasure` `$entity`.... so we're querying for the `$entity` using
the `repository` and the `id` from the DTO class.

Let's inject the services we need: `private EntityManagerInterface $entityManager`
so we can save and `private DragonTreasureRepository $repository`.

There we go! Moment of truth:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPublishTreasure
```

The test *passes*! Heck, at this point, *all* of our treasure tests pass! We've
completely converted this complex API resource to our DTO-powered system.

Next: Let's make it possible to *write* the `$dragonTreasures` property on *user*.
This involves a trick that's going to help us better understand how API Platform
loads data.
