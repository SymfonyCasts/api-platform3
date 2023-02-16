# Query Extension

Coming soon...

When we get a collection of treasures, we're currently returning all of the
treasures, even if they're unpublished. So probably some of these are unpublished
treasures. We did add a filter so that you could control this, but really we need to
not return the published treasures automatically. So if you look for the UP API
platform upgrade guide, we actually looked at this earlier, a search for the word
state. They have a really cool spot here where they talk about providers and
processors. We've already talked about state processors like the persist processor on
the put and post end points, which is actually responsible for saving the item of the
database. But there's also something called the provider, and this is what's
responsible for loading that object. For example, when we make a get request for a
single item, the item provider is what's responsible for taking the ID and loading
that single item. So in this case, the item provider loads that from the database.
There's also a collection provider to load a collection of items. So if we wanted to
hide un unplugged treasures, we could decorate this collection provider and make that
change just like how we've been decorating the persist processor. But one tricky
problem with that is that if we decorated this collection provider, it would make the
query for all of the treasures and then we would have to filter them out. So it's not
great for performance because we might query for a hundred treasures and then filter
half of them out.

So in that case, it's not the best extension point. Fortunately, this collection
provider provides its own extension point that allows us to modify the query before
it's executed. So let's first modify a test to kind of show the behavior we want. So
find, test, get collection of treasures. And what I'm gonna do here is I'm gonna take
control of these five treasures and say, is published true? Because right now in
Dragon Treasure Factory, the is published is set to just a random value. So it might
be true or it might be false. So now we'll have five published dragon treasures and
let's create one more. So I'll say create one and this time, this time let's say is
published false. Awesome. So what we want is we want this still to just return five
items. Let's make sure this fails. So symphony php bin slash php unit dash dash
filter equals the name of our test and awesome. Yep. We can see we are currently
returning all six items. Alright, so to modify the query for a collection endpoint,
we're gonna create something called a query extension. So anywhere in source, but
I'll do it in the APAP platform directory.

Create a new class called How about Dragon Treasure is published extension. We're
gonna make this implement query collection extension interface, and I'll go to co
code generate or command N on the Mac and generate the one method we need, which is
called apply to collection. So it's pretty cool. It passes us the Query builder and a
couple of other pieces of information here and we can modify that query builder. So
this query builders already gonna take into account things like Page Nation and any
filters that have been applied. So those will all be there and we just modify it to
add our custom thing. Now, thanks to Symphony's Auto Configuration System, just
because we have this class and it implements this interface, this is automatically
gonna be called whenever a collection endpoint is being used, and it's gonna be
called for every single resource. The first thing we need to do is say if Dragon
Treasure call on colon class does not equal resource class, so it passes us the class
that it's currently loading right here, then we're just gonna return. So we don't
wanna modify, for example, the user endpoint. All right? Now one of the weird things
inside the query builder is every query builder has an alias that refers to the root

Uh, table that we're working on. So usually inside of a repository class, when you're
creating a custom query, you'll do things like this Arrow query builder d and d
becomes your root alias, and then you need to refer to that other parts in the query
whenever you're doing stuff. In this case, we didn't create the query builder, so we
don't control that root alias, but we can read the root alias by saying query builder
arrow, get root aliases. There is a single one but not the one that's plural and
there's almost all, there should only be one root alias. So we can just use the zero
key. Now it's just normal modification. So query builder and where, and then I use a
sprint app here. This is gonna be a little dynamic because now we need to say percent
S is published equals colon is published, and then pass in the root alias. And then
down here we say set parameter is published true to only return the published once.
All right, let's try that spin over, try your test. It's just that easy. We are now
modifying the collection query. By the way, would this also work for sub resources?
Like for example, over on our documentation, you can see that you can also get
treasures by going to slash api slash users slash user ID slash treasures. Will this
also hide the unpublished treasures there? The answer is yes. So it's not something
you need to worry about, I won't show it, but you are absolutely handled in that
situation as well.

And by the way, if you wanted admin users to be able to see unpublished items, you
could add a little logic here to only add this if this is not an admin. All right,
next, this query extension fixed the collection endpoint, but someone could still
fetch a single treasure by its I unpublished treasure by its id. And that would work.
Fixing time.

