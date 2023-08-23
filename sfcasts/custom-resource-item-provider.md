# Custom Resource Item Provider

Coming soon...

Let's try to get a single item here. I will do this and the identifier. I'll put
today's date when I'm recording this and 200 status code except it returns a
collection. The exact same thing as our collection endpoint. We talked a little bit
earlier about how each operation can have a provider. Right now, when we put provider
under the API resource, this becomes the provider for every operation, which is fine.
You just need to realize that some operations are fetching a collection of resources
and some are fetching in a single item. Inside of our provider, the operation is
what's going to help us see the difference. If we dd operation. I'm actually going to
open this up in a new tab, put .jsonld on the end of it. There we go. It's in this
case, we're getting the get operation. If we did the collection operation, it's get
collection, something that we saw earlier. We can leverage that to figure out the
difference between the two. Very simply, we can say if operation is an instance of
collection operation interface, so all collection operations should implement that,
then we're going to return this error create quests. And down here, now we need the
item operation. So our job here is to find the one operation that we're currently
using. So first of all, you can see that this fixes the collection operation. But we
need to basically somehow get this from the URL so that we can go fetch the one quest
matching that date. To do that, we're going to dump URI variables. And we refresh, we
have a day string inside. So one of the key things is that you notice in our daily
quest, we're not saying what our URL should be. You can do that. But by default, API
platform automatically figures out what the route and URL should look for should look
like. When we run diva router, by default, you can see what it does is it says slash
API slash quests. And then because our identifier is a day string, it puts a curly
brace day string inside of the route. So what's cool about that is that becomes our
one URI variable. And when we have our provider, it's going to pass us any of the URI
variables that were matched in the route. So you can see day string is passed to us.
That makes us dangerous. So in this case, since we're returning since right now we
know our provider down here is acting as the item provider, our job is to return a
single daily quest or no. So I'm going to say quests equals this arrow create quests.
And then we return quests. left square bracket URI variables left record day string
or no. And remember, this works because I made the key the day string for all of our
quests. I did that specifically so I could use this trick of fetching it based on the
day string. Now in a real app, we would want to do this in a more efficient way. It
doesn't really make sense for us to load all of our quests just to return one. But
for our test app, this is going to work fine. And over here, God, it's it returns the
one. And if we did some date that doesn't exist, like 2013, we get the 404 API
platform sees that we return No, it handles doing the 404 for us. Alright, we now
have a fully functional state provider, though, we're going to talk a little bit more
about this later with things like pagination. Next, let's turn to creating a state
processor.
