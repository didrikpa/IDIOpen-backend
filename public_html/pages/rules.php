<h1>
Rules
</h1>

<p><em>If you feel that there is something in the rules that are unclear or need to be added, feel free to send an e-mail to chrischa [at] stud.ntnu.no.</em></p>


<h2>Teams</h2>
<div class="list">
<ul>
<li>A team may consist of a minimum of one and a maximum of three persons.</li>
<li>One person can be a member of at most one team.</li>
</ul>
</div>


<h2>Scoring</h2>

<p>Teams are ranked according to the most problems solved. Teams that are 
tied for amount of problems solved are ranked by least total time and, if 
need be, by the earliest time of submittal of the last accepted run.</p>

<p>The total time is the sum of the time consumed for each problem solved. 
The time consumed for a solved problem is the time elapsed from the 
beginning of the contest to the submittal of the first accepted run plus 
20 penalty minutes for every previously rejected run for that problem. 
There is no time consumed for a problem that is not solved.</p>


<h2>Prize Eligibility</h2>

<p>Check out the <a href="theindex.php?page=description">Contest Description</a> section for information on prize eligibility.</p>

<h2>Computing Constraints</h2>
<div class="list">
<ul>
<li>Each team is allowed to use no more than one specified computer (and it is recommended to do so).</li>
<li>The supported programming languages are Java, C++ and C.</li>
<li>All IDE's are allowed to use (including eclipse, Visual Studio, vim, vi, emacs, TextPad, Notepad, etc).</li>
<li>All (your) submitted code must be written by your own team during the contest.</li>
<li>Your programs may not:
	<ul>
	    <li>access network,</li>
	    <li>read or write files on the system,</li>
	    <li>talk to other processes,</li>
	    <li>fork,</li>
	    <li>or similar stuff.</li>
	    <li>If you try, your program will hang or crash. If it hangs, it will take a couple of minutes before others will be able to run their programs. And please do not crack somebody who uses their spare time trying to give you something valuable.</li>
	</ul>
</li>
</ul>
</div>

<h2>Allowed resources</h2>
<div class="list">
<ul>
    <li>All printed or hand-written material (Books, manuals, handwritten notes, printed notes, etc)</li>
    <li>Pens, pencils, blank paper, stapler and other useful <strong>non-electronic</strong> office equipment.</li>
    <li><a href="http://docs.oracle.com/javase/6/docs/api/index.html">Java 1.6 API</a></li>
    <li><a href="http://www.sgi.com/tech/stl/table_of_contents.html">C++ STL</a> (SGI)</li>
    <li><a href="http://www.cppreference.com/">cppreference.com</a></li>
    <li>MSVS <a href="http://msdn.microsoft.com/library/default.asp?url=/library/en-us/vcstdlib/html/vcoriStandardCLibraryReference.asp">Standard C++ Library Reference</a>
    <ul>
        <li><a href="http://msdn.microsoft.com/library/default.asp?url=/library/en-us/vcstdlib/html/vclrfcpluspluslibraryoverview.asp">Standard C++ Library Overview</a></li>
        <li><a href="http://msdn.microsoft.com/library/default.asp?url=/library/en-us/vclib/html/_crt_run.2d.time_routines_by_category.asp">Run-Time Routines by Category</a></li>
        <li><a href="http://msdn.microsoft.com/library/default.asp?url=/library/en-us/vclib/html/vclrfalphabeticalfunctionreference.asp">Alphabetical Function Reference</a></li>
        <li><a href="http://msdn.microsoft.com/library/default.asp?url=/library/en-us/vclib/html/_crt_global_constants.asp">Global Constants</a></li>
    </ul>
	</li>
    <li>NO other electronic resources/devices.</li>
</ul>
</div>

<h2>Compilers</h2>

<p>gcc -w --std=c99 -O2 -o {BASENAME} {FILENAME} -lm<br>
gcc version 4.4.3 (Ubuntu 4.4.3-4ubuntu5.1)<br> 
<br>
g++ -w -O2 -o {BASENAME} {FILENAME}<br>
gcc version 4.4.3 (Ubuntu 4.4.3-4ubuntu5.1)<br> 
<br>
javac -nowarn {FILENAME}<br>
Run: java {BASENAME}<br>
java version "1.6.0_20"</p>

<h2>Behaviour during the contest</h2>

<p>You should NOT write any code or even touch the problem set until the contest has started.</p>

<p>Contestants are only allowed to communicate with members of their own team and the organisers of the contest. You are not allowed to surf the web (except for allowed content), read e-mail, chat on MSN, or similar things. The only network traffic you may generate is from submitting problem solutions and access content specified by the organisers.</p>


<h2>Submissions</h2>

<p>The different results you could get for your submissions are:</p>

<p><em>Accepted</em><br>
This means that your solution was correct. Congratulations! You just solved the problem :)</p>
<p><em>Wrong answer</em><br>
This means that the answer returned from your program was incorrect, and your algorithm is either incorrect or your program contains a bug (This might also be a minor formatting bug in the output. It might be a good idea to check this out).</p>
<p><em>Timed out or out of memory</em><br>
Your program either used more time or more memory than allowed for this problem.</p>
<p><em>Crashed or out of resources</em><br>
Either your program crashed (error or exception), or it simply ran out of resources (i.e. java heap space)</p>
<p><em>Compile Error</em><br>
Your program did not compile. If it compiles locally, check out the compiler switches shown at
 the bottom of the page. If you think there is an error in the system, please let the judges know.'</p>
<p><em>Internal Error (contact judges)</em><br>
An internal error occurred while running your program. Run off and fetch a judge!
</p>

<h2>Clarifications</h2>

<p>Should you feel that one of the problem statements are ambiguous or are wondering about how something works, you could request a clarification (through the clarification interface on the contest pages). The judges will then review the clarification. If they feel that you should figure out the answer by yourself, the answer will be "No reply. Read problem statement.". Otherwise they will try to answer the question as best they can.</p>

