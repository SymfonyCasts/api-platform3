# Embedding Custom DTO's

On a high level, we want to advertise which treasures you can win from completing
a quest. Embedding an array of `DragonTreasure` objects and showing their IRIs is
a nice way to do that! But it's not the only way.

## Creating the Custom (non-ApiResource) Class

Idea time: what if, instead of pointing to the exact treasures, we simply render
the name, cool factor, and value of each one as a custom array of embedded data?
Check it out. In the `src/ApiResource/` directory, though this class could live
anywhere, create a *new* class called `DailyQuestTreasure`. This will represent
the treasure that you could win by completing a `DailyQuest`.

Inside, create a `public function __construct` and, I'm going to make this very
simple: a `public string $name`, `public int $value` and `public int $coolFactor`.
I'm using public properties for simplicity and even including all three as arguments
to the constructor to make life even easier.

But, I am *not* going to make this an `ApiResource`. Well, we *could* make it an
API resource... if we want our API users to be able to fetch `DailyQuestTreasure`
data directly... or update them. But that's not the point of this class. This will
simply be to be a data structure that we *attach* to `DailyQuest`.

Over in `DailyQuest`, this will no longer hold an array of `DragonTreasure` objects:
it will hold an array of `QuestTreasure` objects. Oh, actually, just to keep the
name a but shorter... there we go... call it `QuestTreasure`... then over here,
`QuestTreasure`.

Now that we have the property set up, head to the provider to set things. Instead
of setting the random dragon treasures onto this *directly*, we need to create
an array of `QuestTreasure` objects. For each over the random treasures as
`$treasure`... then `$questTreasuresp[]` equals new `QuestTreasure` and pass in
the data: `$treasure->getName()`, `$treasure->getValue()` and
`$treasure->getCoolFactor()`. Finish with `$quest->treasures = $questTreasures`.

## "Relations" that are Normal Objects

Before and after this change, our `DailyQuest` class had a property that held an array
of objects. The *key* thing is that, before, it held an array of objects that were,
themselves, API resources. But now, it holds an array of normal, boring objects
that are *not* API resources.

What difference does that make? Check it out. Boom! Embedded objects! When API
Platform serializes the `treasures` property, it sees that our `QuestTreasure` is
*not* an `ApiResource`. So it serializes it like normal Symfony's serializer normally
would: by embedding each property.

This is beautifully simple. And it's something I want you to remember: you can always
create new data classes if you want to embed some extra data.

## The .well-known genId

But I bet you noticed this weird `@id` with `.well-known/genId`. This... is a
randomly-generated string which exists, I believe, basically because JSON-LD
resources are *supposed* to have an `@id`. But since we don't *really* have a place
where you can fetch individual Quest Treasure... API Platform gives us this fake one.

Now, in theory, you could actually turn that off if you want by saying
`#ApiProperty()]` with `genId: false`.

Unfortunately, this doesn't seem to work for array properties... maybe I'm doing
something wrong. I'm still getting that id. But it *does* work for single objects.
To prove it, change this to a single `QuestTreasure`. We don't need our `@var`
anymore because this now has a proper type.

Over in our provider, I'll change a few things *super* quickly... to get just *one*
random `QuestTreasure`. Finish with `$quest->treasure` equals this one `QuestTreasure`.
Use `$randomTreasure` for all of the variable names.

I love it! Now when we refresh... we see *one* embedded object and *no* generated
`@id` field.

Next up: with a custom resource like this, we don't get pagination on our collection
resource automatically. Yup, it's returning *all* 50 items. So let's add that.
