# Relating Resources

In our app, each `DragonTreasure` should be owned by a single dragon... or `User`
in our system. To set this up, forget about the API for a moment and let's just model
that in the database.

## Adding the ManyToOne Relation

Spin over to it and run:

```terminal
php bin/console make:entity
```

Let's modify the `DragonTreasure` entity to add an `owner` property... and then this
will be a`ManyToOne` relation. If you're not sure which relation you need, you can
always type `relation` and it'll ask you some questions to help.

This will be a relation to `User`... and then it asks if the new `owner` property
is allowed to be null in the database. Every `DragonTreasure` *must* have an owner...
so say "no". Next: do we we want to map the other side of the relationship? So
basically, do we want the ability to say, `$user->getDragonTreasures()` in our code?
I'm going to say yes to this. And you might answer "yes" to this for two reasons.
First, if saying `$user->getDragonTreasures()` is a method you want to be able to
call in your code. Or second, as we'll see a bit later, if you want to be able
to fetch a `User` in your API and instantly see what treasures it has. We'll see
that a bit later.

The property - `dragonTreasures` inside of `User` is fine.... and finally, for
`orphanRemoval`, say no. We'll also talk about that later.

And... done! Hit enter to exit./

So this has nothing to do with API Platform. Our `DragonTreasure` entity now has
a new `owner` property with new `getOwner()` and `setOwner()` methods. And over in
`user` we have a new `dragonTreasures` property, which is a `OneToMany` back to
`DragonTreasure`. At the bottom, that generated `getDragonTreasures()`,
`addDragonTreasure()`, and `removeDragonTreasure()`. Very standard stuff.

Ok, let's create a migration for this:

```terminal
symfony console make:migration
```

We'll do our standard double-check to make sure the migration isn't trying to
mind bitcoin - yep, looks good - then run it with:

```terminal
symfony console doctrine:migrations:migrate
```

## Resetting the Database

And it explodes in our face. Rude! But... it shouldn't be too surprising. We already
have about 40 `DragonTreasure` records in our database. So when the migration tries
to add the `owner_id` column to the table - which does *not* allow null - our
database is stumped: it has no idea what value to put for those existing treasures.

If our app were already on production, we'd have to do a bit more work to fix this.
And we talk about that in our Doctrine tutorial. But since this isn't on production
yet, the easiest thing fix is to turn the database off and on again. To do that run:

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

Finally, re-add some nice data with:

```terminal
symfony console doctrine:fixtures:load
```

And oh, this fails for the same reason! It's trying to create Dragon Treasures
without
an owner. To fix that, there are two options. In `DragonTreasureFactory`, add a new
`owner` field in `getDefaults()` set to `UserFactory::new()`.

I'm not going to go into the specifics of Foundry - and Foundry has great docs
on how to work with relationships - but this will create a *new* `User` each time
it creates a new `DragonTreasure` - and will relate them. So that's nice to have
as a default.

But in `AppFixtures`, let's *override* that to do something cooler. Move the
`DragonTreasureFactory` call after `UserFactory`...then pass a second argument,
which is a way to override the defaults. By passing a callback, each time a
`DragonTreasure` is created - so 40 times - it will call this method and we
can return unique data to use for overriding the defaults. Return
`owner` set to `User::factory()->random()`.

That'll find a random `User` object and set it as the `owner`. So we'll have 40
`DragonTreasure`s each randomly hoarded by one of these 10 `User`s.

Let's try it! Run

```terminal
symfony console doctrine:fixtures:load
```

This time... it's a success.

## Exposing the "owner" in the API

Ok, so now `DragonTreasure` has a new `owner` relation property... and `User`
has a new `dragonTreasures` relation property.

Will... that new `owner` property show up in the API? Try the GET collection endpoint
for treasure. And... the new field does *not* show up! Amnd... that makes sense!
The new `owner` property is *not* inside the normalization group.

So *if* we want to expose the `owner` property in the API, just like any other field,
we need to add groups to it. Copy the groups from `coolFactor`... and paste them
here.

This makes the property readable *and* writable. And yes, later, we'll learn how
to set the `owner` property automatically so that the API user doesn't need to send
that manually. But for now, having the API client send the owner manually will
work great.

Anyways, what does this new `owner` property look like? Hit "Execute" and... woh!
The `owner` property is set to a URL. Well, really, the IRI of the `User`!

I *love* this. When I first started working with API Platform, I thought relationship
properties might just use the object's id. Like `owner: 1`. But this is *way* more
useful... because it tells our API client exactly *how* they could get more information
about this user: just follow the URL!

## Writing a Relation Property

Ok cool. So, by default, a relation is returned as an IRI. But what does it look
like to *set* a relation field? Refresh the page, open the POST endpoint, try it,
and I'll paste in all of the fields *except* for `owner`.

What *do* we use for `owner`? I don't know! Let's try setting it to an id, like `1`.

Moment of truth. Hit execute... let's see... a 400 status code! And check out
the error:

> Expected IRI or nested document for attribute `owner`, integer given.

So I passed the `ID` of the owner... it doesn't like that. What should we put here?
Well, the IRI of course! Let's find out more about that next.
