Compatible with htmLawed as of 1.1.14

HtmlCleaner
===========

> htmLawed.class.php is in a functional state. htmlCleaner.class.php is not (as of 7/3/2012).
> Sample invocation code is in test-suite.php until a permanent method has been agreed upon.

A refactoring (to a class) of PHP Labware's fantastic  <a href="http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/index.php">htmLawed function library</a>.

**Why?**

Because while htmLawed is a wonderful alternative to HTMLTidy, the source code itself is a bit difficult to read
and understand. Variable name refactoring, structured whitespace, and conversion into a class file makes the 
project easier to understand and integrate.

**What Else?**

The goal of this is not to affect functionality of the htmLawed script, just code readability. Some functionality 
has been commented out of the class file because of requirements of our original goal for this, but that 
functionality still exists and should be in good working order. The only major things that are heavily commented 
are the master tag list (only certain tags are uncommented) and hl_tag2.

**Last little bit**

The original htmLawed is avaialble through Dual licensed with 
<a href="http://www.gnu.org/licenses/lgpl.html">LGPL 3</a> and 
<a href="http://www.gnu.org/licenses/gpl-2.0.html">GPL 2</a> or later, and so is this.

<a href="http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/htmLawed_README.htm#s4.3">See the htmLawed changelog for all the latest updates.</a>

The original htmLawed is copyrighted by Santosh Patnaik, MD, PhD.