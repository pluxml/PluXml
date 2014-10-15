<p>Below is just about everything you&#8217;ll need to style in the theme. Check the source code to see the many embedded elements within paragraphs.</p>
<hr />
<h1>Heading 1</h1>
<h2>Heading 2</h2>
<h3>Heading 3</h3>
<h4>Heading 4</h4>
<h5>Heading 5</h5>
<h6>Heading 6</h6>
<hr />
<p>Lorem ipsum dolor sit amet, <a title="test link" href="#">test link</a> adipiscing elit. <strong>This is strong.</strong> Nullam dignissim convallis est. Quisque aliquam. <em>This is emphasized.</em> Donec faucibus. Nunc iaculis suscipit dui. 5<sup>3</sup> = 125. Water is H<sub>2</sub>O. Nam sit amet sem. Aliquam libero nisi, imperdiet at, tincidunt nec, gravida vehicula, nisl. <cite>The New York Times</cite> (That&#8217;s a citation). <span style="text-decoration:underline;">Underline.</span> Maecenas ornare tortor. Donec sed tellus eget sapien fringilla nonummy. Mauris a ante. Suspendisse quam sem, consequat at, commodo vitae, feugiat in, nunc. Morbi imperdiet augue quis tellus.</p>
<p><abbr title="Hyper Text Markup Language">HTML</abbr> and <abbr title="Cascading Style Sheets">CSS</abbr> are our tools. Mauris a ante. Suspendisse quam sem, consequat at, commodo vitae, feugiat in, nunc. Morbi imperdiet augue quis tellus.  Praesent mattis, massa quis luctus fermentum, turpis mi volutpat justo, eu volutpat enim diam eget metus. To copy a file type <code>COPY <var>filename</var></code>. <del>Dinner&#8217;s at 5:00.</del> <ins>Let&#8217;s make that 7.</ins> This <span style="text-decoration:line-through;">text</span> has been struck.</p>
<hr />
<h2>List Types</h2>
<h3>Definition List</h3>
<dl>
<dt>Definition List Title</dt>
<dd>This is a definition list division.</dd>
<dt>Definition</dt>
<dd>An exact statement or description of the nature, scope, or meaning of something: <em>our definition of what constitutes poetry.</em></dd>
</dl>
<h3>Ordered List</h3>
<ol>
<li>List Item 1</li>
<li>List Item 2
<ol>
<li>Nested list item A</li>
<li>Nested list item B</li>
</ol>
</li>
<li>List Item 3</li>
</ol>
<h3>Unordered List</h3>
<ul>
<li>List Item 1</li>
<li>List Item 2
<ul>
<li>Nested list item A</li>
<li>Nested list item B</li>
</ul>
</li>
<li>List Item 3</li>
</ul>
<hr />
<h2>Table</h2>
<table>
<tbody>
<tr>
<th>Table Header 1</th>
<th>Table Header 2</th>
<th>Table Header 3</th>
</tr>
<tr>
<td>Division 1</td>
<td>Division 2</td>
<td>Division 3</td>
</tr>
<tr class="even">
<td>Division 1</td>
<td>Division 2</td>
<td>Division 3</td>
</tr>
<tr>
<td>Division 1</td>
<td>Division 2</td>
<td>Division 3</td>
</tr>
</tbody>
</table>
<h2>Preformatted Text</h2>
<p>Typographically, preformatted text is not the same thing as code. Sometimes, a faithful execution of the text requires preformatted text that may not have anything to do with code. Most browsers use Courier and that&#8217;s a good default &#8212; with one slight adjustment, Courier 10 Pitch over regular Courier for Linux users. For example:</p>
<pre>"Beware the Jabberwock, my son!
    The jaws that bite, the claws that catch!
Beware the Jubjub bird, and shun
    The frumious Bandersnatch!"</pre>
<h3>Code</h3>
<p>Code can be presented inline, like <code>&lt;?php echo "This is my first static page"; ?&gt;</code>, or within a <code>&lt;pre&gt;</code> block. Because we have more specific typographic needs for code, we&#8217;ll specify Consolas and Monaco ahead of the browser-defined monospace font.</p>
<pre><code>
#container {
	float: left;
	margin: 0 -240px 0 0;
	width: 100%;
}</code></pre>
<hr />
<h2>Blockquotes</h2>
<p>Let&#8217;s keep it simple. Italics are good to help set it off from the body text (and italic Georgia is lovely at this size). Be sure to style the citation.</p>
<blockquote><p>Good afternoon, gentlemen. I am a HAL 9000 computer. I became operational at the H.A.L. plant in Urbana, Illinois on the 12th of January 1992. My instructor was Mr. Langley, and he taught me to sing a song. If you&#8217;d like to hear it I can sing it for you.</p>
<p><cite>— <a href="http://en.wikipedia.org/wiki/HAL_9000">HAL 9000</a></cite></p></blockquote>
<p>And here&#8217;s a bit of trailing text.</p>