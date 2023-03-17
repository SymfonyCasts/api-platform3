# Allow Admin Users to Edit any Treasure

We've got things set up so that only the owner of a treasure can edit it. *Now*,
a new requirement has come down from on-high: admin users should be able to edit
*any* treasure. That means a user that has `ROLE_ADMIN`.

To the test-mobile! Add a `public function testAdminCanPatchToEditTreasure()`.
Then create an admin user with `UserFactory::createOne()` passing roles set to
`ROLE_ADMIN`.

## Foundry State Methods

That'll work fine. But if we need to create a lot of admin users in our tests,
we can add a shortcut to Foundry. Open `UserFactory`. We're going to create something
called a "state" method. Anywhere inside, add a public function called, how about
`withRoles()` that has an `array $roles` argument and returns `self`, which will
make this more convenient when we use it. Then
`return $this->addState(['roles' => $roles])`.

Whatever we pass to `addState()` becomes part of the data that will be used to
make this user.

To use the state method, the code changes to `UserFactory::new()`. Instead of creating
a `User` object, this instantiates a new `UserFactory`... and then we can call
`withRoles()` and pass `ROLE_ADMIN`.

So, we're "crafting" what we want the user to look like. When we're done, call
`create()`. `createOne()` is a static shortcut method. But since we have an
instance of the factory, use `create()`.

But we can go even further. Back in `UserFactory`, add another state method called
`asAdmin()` that returns `self`. Inside return `$this->withRoles(['ROLE_ADMIN'])`.

Thanks to that, we can simplify to `UserFactory::new()->asAdmin()->create()`.
Nice!

## Writing the Test

*Now* let's get this test going. Create a new `$treasure` set to
`DragonTreasureFactory::createOne()`.

Because we're not passing an `owner`, this will create a new `User` in the background
and use *that* as the `owner`. This means that our admin user will *not* be the
owner.

Now, `$this->browser()->actingAs($adminUser)` then `->patch()` to
`/api/treasures/` `$treasure->getId()`, sending `json` to update `value` to the
same `12345`. `->assertStatus(200)` and `assertJsonMatches()` `value`, `12345`.

Cool! Copy the method name. Let's try it:

```terminal
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

And... okay! We haven't implemented this yet, so it fails.

## Allowing Admins to Edit Anything

So, how *do* we allow admins to edit any treasure? Well, at first, it's relatively
easy because we have total control via the `security` expression. So we can add
something like `if is_granted("ROLE_ADMIN") OR` and then put parentheses around the
other use-case.

Let's make sure it works!

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

A 500 error! Let's see what's going on. Click to open this.

> Unexpected token "name" around position 26.

So... that was an accident. Change `OR` to `or`. Then try the test again:

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

Got it! But my screw-up brings up a great point: the `security` expression is
getting *too* complex. It's about as readable as a single-line PERL script... and
we do *not* want to make mistakes when it comes to security.

So next, let's centralize this logic with a voter.
