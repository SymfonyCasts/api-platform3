# Relating Resources

In our app, each `DragonTreasure` should be owned by a single dragon... or `User`
in our system. To set this up, forget about the API for a moment and let's just model
this in the database.

## Adding the ManyToOne Relation

Spin over to your terminal and run:

```terminal
php bin/console make:entity
```

Let's modify the `DragonTreasure` entity to add an `owner` property... and then this
will be a `ManyToOne` relation. If you're not sure which relation you need, you can
always type `relation` and get a nice little wizard.

This will be a relation to `User`... and then it asks if the new `owner` property
is allowed to be null in the database. Every `DragonTreasure` *must* have an owner...
so say "no". Next: do we we want to map the other side of the relationship? So
basically, do we want the ability to say, `$user->getDragonTreasures()` in our code?
I'm going to say yes to this. And you might answer "yes" for two reasons. Either
because being able to say `$user->getDragonTreasures()` would be useful in your
code *or*, as we'll see a bit later, because you want to be able to fetch a
`User` in your API and instantly see what treasures it has.

Anyways, the property - `dragonTreasures` inside of `User` is fine.... and finally,
for `orphanRemoval`, say no. We'll also talk about that later.

And... done! Hit enter to exit.

So this had nothing to do with API Platform. Our `DragonTreasure` entity now has
a new `owner` property with `getOwner()` and `setOwner()` methods. 

[[[ code('9a8f09be4c') ]]]

And over in `User` we have a new `dragonTreasures` property, which is a `OneToMany` back to
`DragonTreasure`. At the bottom, it generated `getDragonTreasures()`,
`addDragonTreasure()`, and `removeDragonTreasure()`. Very standard stuff.

[[[ code('5de6cf009c') ]]]

Let's create a migration for this:

```terminal
symfony console make:migration
```

We'll do our standard double-check to make sure the migration isn't trying to
mine bitcoin. Yep, all boring SQL queries here. 

[[[ code('dedb502552') ]]]

Run it with:

```terminal
symfony console doctrine:migrations:migrate
```

## Resetting the Database

And it explodes in our face. Rude! But... it shouldn't be too surprising. We already
have about 40 `DragonTreasure` records in our database. So when the migration tries
to add the `owner_id` column to the table - which does *not* allow null - our
database is stumped: it has no idea what value to put for those existing treasures.

If our app were already on production, we'd have to do a bit more work to fix this.
We talk about that in our Doctrine tutorial. But since this *isn't* on production,
we can cheat and just to turn the database off and on again. To do that run:

```terminal
symfony console doctrine:database:drop --force
```

Then:

```terminal
symfony console doctrine:database:create
```

And the migration, which *should* work now that our database is empty.

```terminal
symfony console doctrine:migrations:migrate
```

## Setting up the Fixtures

Finally, re-add some data with:

```terminal
symfony console doctrine:fixtures:load
```

And oh, this fails for the same reason! It's trying to create Dragon Treasures
without an owner. To fix that, there are two options. In `DragonTreasureFactory`,
add a new `owner` field to `getDefaults()` set to `UserFactory::new()`.

[[[ code('5e89f3ad8c') ]]]

I'm not going to go into the specifics of Foundry - and Foundry has great docs
on how to work with relationships - but this will create a *new* `User` each time
it creates a new `DragonTreasure`... and then will relate them. So that's nice to
have as a default.

But in `AppFixtures`, let's *override* that to do something cooler. Move the
`DragonTreasureFactory` call after `UserFactory`... then pass a second argument,
which is a way to override the defaults. By passing a callback, each time a
`DragonTreasure` is created - so 40 times - it will call this method and we
can return unique data to use for overriding the defaults for that treasure. Return
`owner` set to `UserFactory::random()`:

[[[ code('e87c62ef4b')]]]

That'll find a random `User` object and set it as the `owner`. So we'll have 40
`DragonTreasure`s each randomly hoarded by one of these 10 `User`s.

Let's try it! Run:

```terminal
symfony console doctrine:fixtures:load
```

This time... success!

## Exposing the "owner" in the API

Ok, so now `DragonTreasure` has a new `owner` relation property... and `User`
has a new `dragonTreasures` relation property.

Will... that new `owner` property show up in the API? Try the GET collection endpoint
for treasure. And... the new field does *not* show up! That makes sense!
The `owner` property is *not* inside the normalization group.

So *if* we want to expose the `owner` property in the API, just like any other field,
we need to add groups to it. Copy the groups from `coolFactor`... and paste them
here.

[[[ code('554977e73d') ]]]

This makes the property readable *and* writable. And yes, later, we'll learn how
to set the `owner` property automatically so that the API user doesn't need to send
that manually. But for now, having the API client send the  `owner` field will
work great.

Anyways, what does this new `owner` property look like? Hit "Execute" and... woh!
The `owner` property is set to a URL! Well, really, the *IRI* of the `User`.

I *love* this. When I first started working with API Platform, I thought relationship
properties might just use the object's id. Like `owner: 1`. But this is *way* more
useful... because it tells our API client exactly *how* they could get more information
about this user: just follow the URL!

## Writing a Relation Property

So, by default, a relation is returned as a URL. But what does it look like to
*set* a relation field? Refresh the page, open the POST endpoint, try it, and
I'll paste in all of the fields *except* for `owner`. What *do* we use for `owner`?
I don't know! Let's try setting it to an id, like `1`.

Moment of truth. Hit execute. Let's see... a 400 status code! And check out
the error:

> Expected IRI or nested document for attribute `owner`, integer given.

So I passed the `ID` of the owner and... it doesn't like that. What *should* we
put here? Well, the IRI of course! Let's find out more about that next.
