# Embedded Object

Coming soon...

On a high level, we want to advertise which treasures you can win from completing a
quest. Embedding an array of Dragon Treasure objects and it showing as their IRIs is
a nice way to do that, but it's not the only way. What if, instead of pointing to the
exact treasures, we simply want to advertise the name, cool factor, and value of each
one as an array of embedded objects? Check it out. In the source API resource
directory, though it doesn't need to live here, I'm going to create a new class
called DailyQuestTreasure. This is going to represent information about the treasure
that you could win by completing a daily quest. Inside of it, I'm going to create a
public function __construct. I'm going to make this very simple. I'm going to create
public string name, public int value, public int cool factor. I'm using public
properties for simplicity, and I'm even including all three as arguments in the
constructor, just to make this a little bit easier. Super easy to deal with. This
isn't going to be an object that we ever update or anything. It's just going to hold
data for us. Notice, I am not going to make this an API resource. We could make it an
API resource, and we would do that if we need to be able to fetch these DailyQuest
Treasure objects directly or create new DailyQuest Treasures or update them, but
that's not the point of this class. This is just going to be a structure that we
attach to our DailyQuest. Check it out. Over in DailyQuest, this is no longer going
to be an array of dragon treasures. This is going to be an array of Quest Treasures.
Oh, actually, just to keep my name a little shorter, there we go. Let's call this
Quest Treasure, and then over here, Quest Treasure. Cool. Then over in our provider,
we're going to update things accordingly. So instead of setting the random dragon
treasures onto this directly, I'm going to create a new Quest Treasures array. We're
going to for each over the random treasures as treasure, and we'll say Quest
Treasures, left square bracket, right square bracket equals new Quest Treasure, and
then we're just going to kind of grab the data from the treasure object. So the name,
which is treasure arrow, get name, treasure arrow, get value, and treasure arrow, get
cool factor. And then down here, we'll say Quest arrow treasures equals Quest
Treasures. So we've changed our treasures from an array of objects that are an API
resource to just an array of random objects that we invented. And check out the
results. Boom, beautiful. So when it serializes our treasures property, it sees that
our Quest Treasure is not an API resource. So it just serializes it like normal, it
serializes it, its actual data shows up here. So that's beautifully simple. And it's
something I want you to remember, you can always just create the data structures that
you want to embed extra data. Now, you might be looking at this at ID here thing,
this weird dot well known gen ID. And in fact, that actually will change randomly
every time we refresh. So this is JSON ID needs every resource to have an ID. But we
can't actually we don't actually have a place where we can fetch individual Quest
Treasures. This doesn't really have an IRI. And so API platform kind of generates one
automatically for you with this kind of gen ID thing. Now, in theory, you can
actually turn that off if you don't really want that doesn't hurt anything. But you
can say API property, and then gen ID equals false. Unfortunately, this doesn't seem
to work for array properties, I might just be doing it wrong, there might be a better
way to do it. But you can see I'm still getting the gen ID. But it does work for
single objects. So let me just approve that. Let's change this to a single quest
treasure. We don't need our app bar anymore, since we have a proper type on that. And
then over in our provider, we'll just change a few things, we'll just get a single
random treasure out of this. We don't need an array anymore. We'll set quest arrow
treasure equal to the one quest treasure. And then we use random treasure for all of
those variables. Perfect. Now we go over here and refresh, you can see embeds one of
them. And that gen ID did get rid of the ID inside there, we said doesn't really hurt
anything. But just be aware that that's gonna happen on there. Alright, next up with
a custom resource like this one, we don't get pagination on our collection resource
automatically, you can see this is returning all 50 of our items. So let's add it.
