<h1>Programming Tips</h1>

<p>Here are some useful code snippets for programming contests like IDI Open. Feel free to use these during the contest. Remember that copy/paste from electronic sources is not allowed, so we recommend you print these out and bring to the contest for re-typing.</p>

<h2>Reading input</h2>
	<p>
		One way of dealing with input in a contest, is through the examplecode below. The methods readInt() and readString() returns the next int/String. If there are no more tokens on the line, it tokenizes the next line and returns the first int/String. The StringTokenizer (Java) is a class that takes one String as input to the constructor, and splits it into tokens/substrings (whitespace marks the place to split). You can call nextToken() on the StringTokenizer to get the next token. In C++ we simply use cin for input.
	</p>

<p>Java:</p>
<div class="code">
<em class="keyword">import</em>&nbsp;java.io.*;<br>
<em class="keyword">import</em>&nbsp;java.util.*;<br><br>

<em class="keyword">public</em>&nbsp;<em class="keyword">class</em>&nbsp;Example&nbsp;{<br>
<div class="indent"><em class="keyword">static</em>&nbsp;BufferedReader&nbsp;stdin&nbsp;=&nbsp;<em class="keyword">new</em>&nbsp;BufferedReader(<br>
<div class="indent"><div class="indent"><em class="keyword">new</em>&nbsp;InputStreamReader(System.in));<br>
</div></div>

<em class="keyword">static</em>&nbsp;StringTokenizer&nbsp;st&nbsp;=&nbsp;<em class="keyword">new</em>&nbsp;StringTokenizer(<em class="string">""</em>);<br>
<br>
<em class="keyword">public</em>&nbsp;<em class="keyword">static</em>&nbsp;<em class="keyword">void</em>&nbsp;main(String[]&nbsp;args)&nbsp;<em class="keyword">throws</em>&nbsp;Exception&nbsp;{<br>

<div class="indent"><em class="string">// Your code here.</em><br>
</div>
}<br>
<br>
<em class="string">// Read next input-token as string.</em><br>
<em class="keyword">static</em>&nbsp;String&nbsp;readString()&nbsp;<em class="keyword">throws</em>&nbsp;Exception&nbsp;{<br>
<div class="indent"><em class="keyword">while</em>&nbsp;(!st.hasMoreTokens())&nbsp;{<br>

<div class="indent">st&nbsp;=&nbsp;<em class="keyword">new</em>&nbsp;StringTokenizer(stdin.readLine());</div>
}<br>
<em class="keyword">return</em>&nbsp;st.nextToken();</div>
}<br>
<br>
<em class="string">// Read next input-token as integer.</em><br>
<em class="keyword">static</em>&nbsp;<em class="keyword">int</em>&nbsp;readInt()&nbsp;<em class="keyword">throws</em>&nbsp;Exception&nbsp;{<br>

<div class="indent"><em class="keyword">return</em>&nbsp;Integer.parseInt(readString());</div>
}<br><br>
<em class="string">// Read next input-token as double.</em><br>
<em class="keyword">static</em>&nbsp;<em class="keyword">double</em>&nbsp;readDouble()&nbsp;<em class="keyword">throws</em>&nbsp;Exception&nbsp;{<br>
<div class="indent"><em class="keyword">return</em>&nbsp;Double.parseDouble(readString());</div>

}</div>
}
</div>

<p>C++:</p>
<div class="code">
<em class="keyword">#include</em>&nbsp;&lt;iostream&gt;

<br>
<br>

<em class="keyword">using namespace</em> std;
<br />
<br />

<em class="keyword">int</em>&nbsp;main(<em class="keyword">int</em> argc,&nbsp; <em class="keyword">char</em>** args){<br>
<div class="indent"><em class="string">// Your code here.</em></div>
}

<br>
<br>

<em class="string">// Read next input-token as string.</em><br>

string&nbsp;readString() {<br>

<div class="indent">string token;<br />
cin >> token;<br />
<em class="keyword">return</em> token;<br /></div>
}<br>

<br>

<em class="string">// Read next input-token as integer.</em><br>

<em class="keyword">int</em>&nbsp;readInt() {<br>
<div class="indent"><em class="keyword">int</em> token;<br />
cin >> token; <br />
<em class="keyword">return</em> token;</div>
}<br><br>

<em class="string">// Read next input-token as double.</em><br>
<em class="keyword">double</em>&nbsp;readDouble() {<br>
<div class="indent"><em class="keyword">double</em> token;<br /> 
cin >> token; <br />
<em class="keyword">return</em> token;</div>
}<br><br>

</div>

<h2>Formatting floating-point numbers</h2>
<p>When dealing with floating-point numbers, you're often asked to print the result using a given precision, for example 3 digits.</p>
<p><b>Java:</b></p>
<p>One way of doing this in Java is using a class called DecimalFormat, which takes as an argument a string defining the format.</p>
<div class="code">
<em class="keyword">static</em>&nbsp;DecimalFormat&nbsp;DF&nbsp;=&nbsp;<em class="keyword">new</em>&nbsp;DecimalFormat(<em class="string">"0.000"</em>,<br>

<div class="indent"><div class="indent"><em class="keyword">new</em>&nbsp;DecimalFormatSymbols(Locale.ENGLISH));<br>
</div></div>
</div>

<p>
To print in another format, just change the string argument of the DecimalFormat constructor.</p><p>
When we then want to print out the number, we call format() on the DecimalFormat. This method returns a string we print out using System.out.println(). 
</p>
<div class="code">
<em class="keyword">double</em> number = 3.14159226538979;<br>
System.out.println(DF.format(number));
</div>

<p><b>C++:</b></p>
<p>In C++, one way to achieve this is using the setiosflags and setprecision functions from the header iomanip before printing. Here's an example:</p>
<div class="code">
<em class="keyword">#include</em>&nbsp;&lt;iostream&gt;<br />
<em class="keyword">#include</em>&nbsp;&lt;iomanip&gt;
<br />
<br />

<em class="keyword">using namespace</em> std;
<br />
<br />

<em class="keyword">int</em>&nbsp;main(<em class="keyword">int</em> argc,&nbsp; <em class="keyword">char</em>** args){<br>
<div class="indent">cout << setiosflags(ios::fixed) << setprecision(3);
<br /><br />
<em class="keyword">double</em> number = 3.14159226538979;<br /><br />
<em class="string">//This will print 3.142 (that is, "number" rounded to 3 decimals after comma)</em>
cout << number;<br />

</div>
}

</div>
<p>To change the number of decimals again, simply call setprecision again the same way as above, with the desired number of decimals.</p>
