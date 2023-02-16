# Filtered Collection

Coming soon...

We've made a pretty fancy api. We've got a couple of sub resources. We have embedded
data, which is readable and writeable and this is all really cool, but it does start
to make our API APM or complex, especially when it comes to security. For example,
you can no longer see unpublished treasures from the collection endpoint or the get
single endpoint, but you can still see unpublished treasures if you fetch a user and
read its Dragon Treasures Field Watch. Let's write a test for this real quick to
expose it. So open our user resource test and down at the bottom we'll do a public
function test. Unpublished treasures not returned Inside here we need to create a
user with user equals User Factory Call con create one and then Dragon Treasure
Factory. Actually I I'm need that and then Drag Dragon, treasure Factory, create one.
And here we'll say this is gonna be in, is published False. And let's make sure the
owner is set to that user so we know who the owner of that user is. And then down
here we'll say this Arrow browser and we do need to log in to use the endpoint, but
it doesn't matter who we log in as. So I'm gonna say acting as, and we'll say user
factory create one. So I'm logging in as someone else, but then I'm going to get
slash api slash users slash

And then user arrow get id. And down here we're gonna assert that the Jsun matches
and we'll use a cool thing with the syntax. We can say the, the length of the Dragon
Treasures field is zero. Cool, let's try that. I'll copy the test method name. We'll
run our test with dash dash filter equals that test method name. And yeah, it fails.
So expected one to be the same as zero. So we are returning that one unpublished item
there and we don't want to, so why, why is that being returned? Well, an important
thing to understand about these query extension interfaces is that these are used for
the, for the original query. So query collection extension interface that applies to
the collection endpoint only when we're querying for a collection of treasures. But
when you use a user endpoint, what it's first gonna do is query for that user and
once it's queried for that user, in order to get this dragon treasure here, it
doesn't make another query for Dragon Treasures. Instead, if you open these source
entity user class, all it does is called Get Dragon Treasures. So queries for the
user, it calls get Dragon Treasures and whatever this returns is going to be, what is
set onto that field. And since this is gonna return all Dragon treasures, that's what
we get, including the unpublished ones.

So the way to fix this is to add a new method that only returns the unpublished ones.
So public function get published, dragon Treasures. This will return a collection and
inside we can just kind of get fancy here we can say this Arrow Dragon treasures, and
we can use a filter function and then pass that a callback that will get a Dragon
treasure treasure argument. Then inside we can say Return Treasure Arrow Get is
published. So that's just a really fancy way to loop over all the dragon treasures
and return a new collection that only has the published ones. By the way, one
downside to this approach is that if a user has a hundred Dragon treasures, but only
10 of them are published internally, doctrine is first gonna need to query for all
100 treasures simply to return only 10 of them. So if you have possibly have large
collections of Dragon treasures, this can be a performance problem. In our doctrine
tutorial, we talk about using something called the criteria system criteria like
this, and that's a way where you can actually create, do the same thing, but use a
efficient query to only return the unpublished ones.

Anyways, just creating this getter method is not enough. This is not gonna be part of
our api. What we can do now is we can go up to the Dragon Treasure's property. Here
we go. And this is currently readable and writeable. Let's make that property only
writeable. But then down here on our new method, we will say Groups user call and
Reid will make this writeable. And then we'll control its name with serialized name,
dragon Treasures. So we should still get that field back, but it's now gonna be
calling this method. All right, try the test and explore explodes because I have a
syntax air darn it. All right, try the test.

What's going on?

All right, try the test and we're green. All right everyone, thank you for joining.
I'm in this gigantic cool journey with API platform and security. It's almost was
pretty complicated cuz I wanted you to be able to solve real complex security use
cases. In the next tutorial we're gonna look at even more custom and cool things you
can do with the API platform, including how to use classes for API resources that are
not entities and we'll see how that upfront can cause a little bit more work, but how
it can also make your API easier to control and your code easier to read. Our
friends, if there's something that we haven't covered yet that you wanna make sure is
covered in the future tutorial, let us know. Um, as always, we're here for you in the
comment section. See ya.

