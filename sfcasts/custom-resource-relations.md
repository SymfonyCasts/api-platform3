# Custom Resource Relations

Coming soon...

Inside of our daily quest, let's add a new property, public array treasures. This is
going to hold an array of treasures, so dragon treasures, that you can win if you
complete this quest. So at first, this is just like any other, this is really just
like any other property. So in our provider, this is where we're going to populate
that, so we're just going to say quest arrow treasures right here and set that to
something. But I don't want to set it to an empty array, I want actually dragon
treasure objects, right? So let me go to the top and we'll add a construct method.
Inside of there, we'll say private dragon treasure repository, treasure repository.
And down here, I'm just going to kind of do a little bit of randomness. So first
thing I'm going to do is I'm going to search, I'm going to query, say treasures
equals this arrow, treasure repository arrow, arrow find by. I'm going to kind of
abuse this a little bit. The first argument's criteria, I'm going to give it none, no
order by, and then limit 10. So I'm basically just finding the first 10 treasures in
the database, and then I'm going to kind of create a random set down here to give to
each one. So this is kind of some boring code. So first, I get one between rent
between one and three random treasure keys. I'm going to use a fancy array map
function with the even fancier fn syntax. So turn those random keys into random
dragon treasure objects. And that is what we will set up. Right there. Perfect. And
though we don't really care right now, just to make sure our test keeps passing. At
the top of this, say dragon treasure factory, colon, colon, create many five. I'm
just doing this because otherwise, it'll fail and explode if we don't have any
treasures available. But not really worried about the test right now. What I really
want us to just see is if this new field shows up in my API. So to check that out,
I'm actually just going to go to slash API slash quest dot JSON LD directly. And we
get a very strange error, you must call set is owned by authenticated user before is
owned by authenticated user. So this is coming from dragon treasure. All the way at
the bottom. It happens when you call the is owned by authenticated user before ever
calling the set is owned by authenticated user. So what's going on here, for some
reason, when we are serializing the daily quest, it's apparently serializing the
dragon treasures. And it's calling this field. Now normally, we're populating it when
we fetch a dragon treasure directly that when that's the top level object, we are
actually setting that field in a custom dragon treasure provider. But when it's an
embedded object, the provider isn't called and so that field doesn't set. But hold on
a second, this shouldn't even be a problem. And I want to show you what I mean by
that. So to temporarily silence this error, and kind of understand what's going on.
I'm gonna find that property. There it is. And to silence the air, I'm just going to
give it a default value of false. Now spin over and refresh. And whoa, so it works.
Here is our daily quest. And here are the treasures. But this is not actually what we
expected. You can see that the each treasure is as is an embedded object. But why? If
you remember correctly, when you have a relationship, it's only embedded if the
parent and child share groups. So for example, we're not even using any groups. So
the only way this treasure should be embedded is if we were, for example, using
normalization context here with groups set to quest read like this. And we had that
quest read group above treasures. And in dragon treasure, we had at least one
property that also had that quest colon greed on it. Unless you have groups that are
cascading down like that. API platform should render this as an IRA string as an
array of IRA strings, not embedded objects. So what the heck is going on? So the
problem is that the serializer looks at this treasure property and doesn't realize
that this property represents an array of dragon treasure objects. So it doesn't know
that this actually holds an array of objects that are themselves API resource
objects. And so it tries to serialize this like just a normal set of objects. And so
it serializes them recur, serializes all of their properties. And this isn't normally
a problem with entities. Because the serializer is smart enough to read the doctrine
relationship metadata and figure out that the property is a collection of some other
API resource object. So this is really simple to fix. It's just kind of hard to
understand at first, we just need to give it some PHP documentation to help it know
what this is. So for example, at var dragon treasure, left square bracket, right
square bracket. And as soon as we do that, bam, now they are I our eyes. And I won't
do it. But you could even undo the default value now because this object is not going
to be serialized. It's just using the IRA from it. So it wouldn't try to serialize
this property, which is what originally gave us that error. So yeah, you got it.
betting objects is no big deal. Except you need to have a little documentation on
there for the collection case. However, if we want to show off the treasures that a
dragon could win for completing this quest, we don't need to embed the dragon
treasures directly, we could embed some new class with some new information about
those dragon treasures. Let me show you what I mean next.
