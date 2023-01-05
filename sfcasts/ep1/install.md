## Installing API Platform

Hello and welcome you beautiful people to a tutorial that's near and dear to my
heart, how to build really cool castles with Legos. Okay, that would be awesome. But
you know, we're really here to talk about API platform version three, which is
seriously just as fun as playing with Legos. Just don't tell my son I said that API
platform is very simply a tool built on top of Symfony that allows us to build
powerful APIs and absolutely love the process at this point. It's been around for a
while and quite honestly it's absolutely crushing it. The latest version three is
another huge step forward. Now, you might be thinking, Hey, creating an API endpoint
isn't that hard. It's just returning some J S O. And yeah, that is true, at least for
the first few endpoints. But there are a lot of little details to keep track of. For
example, if you have an API that returns product data, you need to make sure that the
product jsun has always returned in the same way with the same fields no matter which
endpoint we're using. That process is called serialization. On top of that, a lot of
APIs now returned extra information along with that data that describes what the data
means. We're going to see and talk about something called Jsun ld, which does exactly
that.

Of course, if you want a really nice api, you're also going to want documentation
that's ideally interactive and generated automatically, cuz we do not want to build
and maintain that by hand. Even if you have a private api, having a documentation is
awesome. What else? How about pagination? Like if you have a lot of products or
filtering, being able to search those products or validation or content type
negotiation, which is where that same product could be returned maybe as J S O N or
CSV or some other format. So yes, creating an a p endpoint is easy, but creating a
rich API is a whole other thing, and that's the purpose of API platform. If you're
familiar with AAP platform from version two, version three will feel very familiar.
It's just cleaner, more moderate, and more powerful. So buckle up and let's do this.
If you go to the API platform page and click into a documentation, okay. There are
two ways to install API platform. If you find their site and click into the
documentation, you'll see them talk about the API platform distribution. This is
pretty cool. It's a completely pre-made project

With Docker that's going to give you a place to build your API with Symfony, a React
admin and scaffolding to build a next JS front end and even a web server on top of
that. It's the most powerful way to use API platform. However, in this tutorial, we
are not going to use this. Instead, we're going to install API platform into a new,
perfectly boring and normal Symfony project because I want you to see exactly how
everything works. Then later, if you want to, you can check out this distribution and
use it. Alright, so to be a true API platform JSON Throwing Champion, you should
totally code along with me. You can download the chorus code from this page. After
unzipping it, you'll find a start directory with the same code that you see here.
This is a normal, boring, brand new Symfony 6.2 project with absolutely nothing in
it, and you can open up this read me.md file for all the setup instructions. The last
step will be to open the project in the terminal and use the Symfony binary to run
Symfony serve-D to start a local web server at 1 27 0 0 1 8,000. I'll cheat and click
that link

To open up as I promised a completely empty new Symfony 6.2 project. There's nothing
yet here except for this demo homepage. Our job is going to be to create a really
rich API for a project I'll talk about in a few minutes. But we are going to create
an application

Where,

Where Dragons can come and post all about the treasures that they have plundered.
Because if there's one thing a dragon likes more than TR than treasure, it's bragging
about it. So our job was to create a rich API that lets Tech Savvy Dragons post new
treasures, fetch treasures, search treasures from other dragons and more. Alright, so
let's get API platform installed. Spin back over to your terminal and run composer
require api. This is a Symfony Flex Alias. You can see up here it's actually
installing something called the API platform pack. If you're not familiar, a pack and
Symfony is just a, it's kind of a fake package that allows you to easily install a
set of packages. In fact, over here, if I look at the composer, that Jsun file, you
can see that it added, oh, that's not do that. So this installed APAP platform itself
as well as doctrine. Cause I didn't have that installed and several other packages at
the bottom. Let's see. The doctrine bundle recipe is asking us if we want to include
a Docker composed YAML file to help us add a database to our project. That's
optional, but I'm going to say P for yes permanently

And perfect. All right, so the first thing to see is in the composer Jsun file. As
promised that API platform pack installed a bunch of items into our project. These
aren't all technically required, but this is going to give us a really rich
experience building our api. It's everything that we need and if you run and get
status at your terminal,

Yep,

It updated the usual files and also added a bunch of configuration files for those
new packages. It looks like there's a lot here, but all of these directories are
empty and these are just very simple configuration files. We also have some doctor
composed piles we'll use in the second to spin up our database. We're really on a
high level. What just happened is we deinstalled API platform into our project. The
end result of that is that if you go back to the browser and go to /api, whoa, we now
have an API documentation page. It's empty cuz we don't actually have anything in our
API yet, but this is going to come to life really soon. So next, let's create our
first doctrine entity and expose that to our api. We're going to get a rich set of
API endpoints set up in just a couple of minutes.
