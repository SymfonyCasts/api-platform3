# Embedding Custom DTO's

One goal of the daily quests resource is to showcase the bountiful treasures a
dragon can win by completing a quest. Embedding an array of `DragonTreasure` objects
and showing their IRIs is a nice way to do that! But it's not the only way.

## Creating the Custom (non-ApiResource) Class

Idea time: forget about pointing to the exact treasures. What if we simply render
the name, cool factor, and value of each as a custom array of embedded data?
Check it out. In the `src/ApiResource/` directory, though this class could live
anywhere, create a *new* class called `DailyQuestTreasure`. This will represent
the treasure that you could win by completing a `DailyQuest`.

Inside, create a `public function __construct` with
a `public string $name`, `public int $value` and `public int $coolFactor`.
I'm using public properties for simplicity and even including all three as arguments
to the constructor to make life *even* easier.

[[[ code('e80338d482') ]]]

But, I am *not* going to make this an `ApiResource`. Well, we *could* do that...
if we need our API users to be able to fetch `DailyQuestTreasure` data directly...
or update them. But that's not the point of this class. This will simply be
a data structure that we *attach* to `DailyQuest`.

Over in `DailyQuest`, this will no longer hold an array of `DragonTreasure` objects:
it will hold an array of `QuestTreasure` objects. Oh, actually, to keep things
shorter... there we go... call it `QuestTreasure`... then over here, `QuestTreasure`.

[[[ code('9994cb8186') ]]]

Now that we have the property set up, head to the provider to populate it. Instead
of setting the random dragon treasures onto this *directly*, we need to create
an array of `QuestTreasure` objects. For each over the random treasures as
`$treasure`... then `$questTreasures[]` equals new `QuestTreasure` and pass in
the data: `$treasure->getName()`, `$treasure->getValue()` and
`$treasure->getCoolFactor()`. Finish with `$quest->treasures = $questTreasures`.

[[[ code('50d3dd7daa') ]]]

## "Relations" that are Normal Objects

Before and after this change, our `DailyQuest` class had a property that held an array
of objects. The *key* difference is that, before, it held an array of objects that
were API resources. But now, it holds an array of normal, boring objects that
are *not* API resources.

What difference does that make? Check it out. Boom! Embedded objects! When API
Platform serializes the `treasures` property, it sees that our `QuestTreasure` is
*not* an `ApiResource`. So it serializes it in the normal way:
by embedding each property.

This is beautifully simple. And it's something I want you to remember: you can always
create new data classes if you want to embed some extra data.

## The .well-known genId

But I bet you noticed this weird `@id` with `.well-known/genId`. This... is a
randomly-generated string which exists, I believe, because JSON-LD resources
are *supposed* to have an `@id`. But since we don't *really* have a place
where you can fetch individual Quest Treasures... API Platform gives us this
fake one.

Now, in theory, you could turn that off by saying `#[ApiProperty()]` with
`genId: false`.

[[[ code('6b1c8ea46a') ]]]

Unfortunately, this doesn't seem to work for array properties... maybe I'm doing
something wrong. I get that id. But it *does* work for single objects.
To prove it, change this to a single `QuestTreasure`. We don't need our `@var`
anymore because this now has a proper type.

[[[ code('4a6206a518') ]]]

Over in our provider, I'll change a few things *super* quickly... to get just *one*
random `QuestTreasure`. Finish with `$quest->treasure` equals this one `QuestTreasure`.
Use `$randomTreasure` for all the variable names.

[[[ code('35a83c33e1') ]]]

I love it! Now when we refresh... we see *one* embedded object and *no* generated
`@id` field.

Next up: with a custom resource like this, we don't get pagination on our collection
resource automatically. Yup, it's returning *all* 50 items. So let's add that.
