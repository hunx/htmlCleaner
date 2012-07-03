HtmlCleaner
===========

> htmLawed.class.php is in a functional state. htmlCleaner.class.php is not (as of 7/3/2012).
> Sample invocation code is in test-suite.php until a permanent method has been agreed upon.

A refactoring (to a class) of PHP Labware's fantastic  <a href="http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/index.php">htmLawed function library</a>.

**Why?**

Because while htmLawed is a wonderful alternative to HTMLTidy, the source code itself is a bit difficult to read
and understand. The original project was to convert this functionality into a class file, but conversion of
the original files needs to happen first to ensure that all required functionality is available.

**What Else?**

The goal of this is not to affect functionality of the htmLawed utility, just code readability. During the conversion 
process, some functionality may be affected for testing purposes only, but it will not stay that way for the finished 
product. Any logic/functionality changes will be shunted off into the class file available in this repo.

**Last little bit**

The original htmLawed is avaialble through Dual licensed with 
<a href="http://www.gnu.org/licenses/lgpl.html">LGPL 3</a> and 
<a href="http://www.gnu.org/licenses/gpl-2.0.html">GPL 2</a> or later, and so is this.

The original htmLawed is copyrighted by Santosh Patnaik, MD, PhD.