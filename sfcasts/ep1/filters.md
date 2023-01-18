# Filters

Coming soon...

Some of our dragon treasures are currently published and some are unpublished. That's
thanks to our Dragon Treasure Factory, where we actually just kind of randomly
publish some and randomly don't publish others. Right now, all of them are being
shown being returned from our api. In the future, we're gonna change our API to
automatically hide unpublished treasures so that they never show. But to start, let's
at least make it possible for our API API clients to hide unpublished results if they
want to. To do this, we're gonna leverage something called a filtered API platform.
Comes with a bunch of built, built in filters that allow you to basically search and
filter through the collections of results. So here's how it works above your class at
another, a attribute called API filter.

Now there's typically two things that you need to pass to API filter. The first is
which filter class you want to use. And if you look at the documentation I mentioned,
there's a bunch of 'em. There's buoy filter, we're gonna use Search Filter in a
second. And there are others. In this case, we want Boo Filter because we're allowing
the user to search on a Boolean field. They should be able to choose whether they
want to return results with is published true or is published false. Now notice, get
the one from O RM cause we're using the doctrine O rm and then add call on call
class. The other thing you need to have here is properties set to an array of which
fields or fields you want to search on. So let's do is published. All right, let's go
back to our documentation and check up the GI collection endpoint. When we tried out,
there's a new is published field here, so let's try it empty First I'll just hit
execute and if I scroll all the way down, there we go. Hydro Total Items 40. Now if
we say is published True and hit Execute,

We have hydro total Item 16, it's alive. And check out how the filtering happens.
It's really simple, it's just a query parameter is published equals true. And this is
really cool down here. If you look at the response, we have the Hydra view, which
shows the Page Nation. We also have a new Hydra search. Hydra actually documents this
new way of searching through our content. This basically says, Hey, if you want to,
you can add a question mark is published query parameter to filter these results.
Pretty cool. All right. Now when you read about API filters inside of the API
platform documentation, they pretty much always show it above the class, but you can
also put it above the act. You can put most filters above the property that they
relate to. So I'm gonna copy this API filter, remove it, and let's go down to the IS
published field and I'll add up there. And no surprise when you do this, you don't
need to pass the properties option anymore. That's gonna be built in. So the results
is the same. I won't try it, but if you look at our collection at Point, it still has
is published on there.

All right, what? What else can we do? Well, there's another really handy filter
called a search filter. So let's allow somebody to search on the name property. So
I'll go above the N, I'll go above that property, add APA filter. In this case we
want search filter. And again, get the one from the RM and do colon, colon class. Now
this one does take an extra option. So you can see here that there is, in addition to
that properties argument, there's an argument here called strategy. This doesn't
apply to all filters, but it does apply to this one. We'll stay strategy and then
partial. So what this means is it's gonna allow us to search the name property and
it's gonna be a fuzzy match. We'll be able to put any te, we'll be able to enter
something and if that matches any part of the name, it will return. There's also
exact and other strategies as well that you can read about in the documentation. All
right, so I'm gonna refresh your documentation. Now that collection endpoint has
another field up here. So let's search for rare. Hit Execute. And let's see, down at
the bottom. Awesome. 15 of our results are rare, have rare somewhere in the name.

And again, it works by just and name equals Rare on the url. All right, so let's
also, let's also make the description field searchable as well. And that now shows up
inside of our api. So this is still a fairly simple fuzzy search. If you want
something, um, more complex like Elastic Search, you can hook API platform filters
with Elastic search as well. And you can even create your own custom filters, which
we'll do in a future tutorial. Like for example, maybe you just wanna have a little
question mark. Search equals on your on the URL that searches across many fields. All
right, next, let's see one more filter than a second filter that's a bit different.
Instead of hiding certain results, that filter allows the API user to hide or show
certain fields in the response.

