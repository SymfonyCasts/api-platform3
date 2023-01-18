# Formats

Coming soon...

API platform supports multiple input formats and also output formats. So we know that
we can go to slash api slash treasures jsun to see jsun format or jsun LD or html.
But adding this extension to the end of the URL is just a bit of a hack that API
platform allows us to do. To choose the format that you want the API to return to
you, you're supposed to send an accept header. And this is something that we see when
we try out the documentation. Uc makes a request with accept header set to
application slash LD plus jsun I Setting this header is easy to do in JavaScript and
actually if you don't set it a application slash LD plus jsun is the default one
anyways. A bill form uses three formats out of the box. You can actually see them
down here on the bottom of the documentation page. Why does it support those three
formats outta the box head? Your terminal run bin console, debug config API platform.
And if you look at side here, you can see a formats key which says those three
formats. So this basically says that if the accept header is application slash LD
plus jsun, use the Jsun LD format.

And internally this means that when it's serializing our data, it's gonna tell the
serializer to serialize to Jsun LD or to serialize to Jsun. So I'm gonna add another
format just to see if we can, so to do that, we basically just need to add a new item
to this config, but to make sure that we don't completely replace this config, copy
those formats and then open the config packages directory. We don't have an API
platform dot yaml file yet, so let's create one API underscore platform, yamal, and
then API underscore platform. And then I'll paste those in. And I don't have to do
this, but I'm actually going to get fancy here. There's an alternate like shorter way
that you can do this configuration that looks a little cooler, so I'm gonna quickly
shorten my code to use that shorter format. Awesome.

Now if we go and refresh right now, everything seems to work the same. We have the
same formats below. That's perfect cause we've just repeated the default
configuration. So the new form we're gonna add is another type of jsun called Jsun.
How? So there's standard J S O, and then J S O LD takes standard J S O and adds more
keys and structures to it. A competing standard with Jsun LV is called Jsun. How I
don't particularly use J S o Hal all that often. I'm mostly listening. We're mostly
doing this. So we can see an example of how adding a format looks. So the, if you
look up the content type for Jsun hal, it's supposed to be application slash how plus
J Sig. So as soon as we do that, we refresh it shows nothing. I'm pretty sure this is
because Symphony is not seeing my new config file. Let's run over here and do a bin,
cast bin console, cash clear refresh again, there we go. JSON hal. And if we click
this, we actually go to the JSON how version of our documentation. But what I wanna
do here is actually do my get request, get collection tryouts. And

Down here you can actually select which of the types you want. So I'll type
application slash how plus JSON Hit execute. And there it is. So you can see it's
json, it's got our same results. It just looks a little bit different. It has things
like un underscore embedded and underscore links. We're not gonna talk much about how
it's not that important. Just wanted to see, show you what it would look like to add
a new format to the system. And the reason this works out of the box is very simply
because the serializer understands the how Jsun format. And this is one of the built
in jsun how formats. Now that's actually because API platform adds that to the
serializer. All right, so let's do something that's maybe a little more practical.
What if we want our users to be able to return the treasures in CSV format? CSV is a
format that Symphony Serializer understands out of the box. So obviously we could add
CSV right here, but as an extra challenge, instead of enabling CSV for every API
resource in our system, let's just add it for Dragon Treasure. So the way you do that
is inside of the API resource key, uh, attribute,

Add a new formats, conf configure. And just like with the configuration, if you just
put like CSV here, that's gonna remove all the other formats. So we actually need to
list them all. Jason ld, we want Jason ld, we want json, we want html, we want json,
hal, and,

And all four of these will read the configuration to know which content type to use.
And then for the last one, we'll do csv. But in this case, since this doesn't exist
in our configuration, we need to tell it what content type should activate this
format. So we'll set that to text slash csv. Now notice my editor is mad at me here.
It says, name to arguments ordered does not match parameters order. Remember, when
you're using attributes, when you're passing arguments to attributes, this attribute
is actually its own class. And what we're doing is just filling in the structor
arguments via named arcs. I actually don't, the order doesn't matter, and I actually
don't think that Peach three Storm should be highlighting this as a problem. But if
you want, we can click sort arguments and there move formats up a little higher and
now it's happy and I won't get drove crazy by that yellow underline. Anyways, when we
go over and refresh now and head it over to our collection endpoint, hit try it out
and down here we will do text slash csv. Hit execute and Hello csv. Dang, that was
easy.

And again, this works because Symphony CER understands the CSV format and all this is
handled by the Sea utilizer. In fact, if I open the profiler for that request, we can
go down to the fertilizer configuration. And yep, you can see it's taking our, it's
formatting through the CSV format. It's using the CSV encoder. It's a built-in
encoder to do that, and that's why we get our nice results. If you want to, you can
also add more custom formats to the system if you want return things in some other
way. All right, next, let's talk about validation.

