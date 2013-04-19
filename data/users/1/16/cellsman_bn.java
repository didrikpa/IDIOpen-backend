/*
Solution to 'Travelling Cellsperson' by Børge Nordli, for IDI Open 2013

The solution can be divided into three cases:
1) X = 1 or Y = 1:
  (For symmetry, assume X = 1.)
  The cellsperson must walk back and forth the "corridor",
  and the minimum number of steps is therefore 2*(Y - 1).
2) X*Y is an even number:
  (For symmetry, assume X is even.)
  If the cellsperson should visit every cell, the path must have
  length at least X*Y.
  By following the system below, the cellsperson can follow a path
  which visits each cell exactly once, and the minimum number of steps
  is therefore exactly X*Y:

  /----....----\
  |/\/\..../\/\|
  |||||....|||||
  |||||....|||||
  .            .
  .            .
  |||||....|||||
  |||||....|||||
  \/\/\..../\/\/
3) Else:
   By coloring the grid like a chess board (alternating white
   and black cells), we can see that the cellsperson must
   alternate between white and black cells. Any path starting and
   ending in the same cell must therefore have an even number of steps.
   By following the system below, the cellsperson can follow a path
   which visits each cell except for one (*) exactly once, and the minimum
   number of steps is therefore exactly X*Y + 1:

  /----....----*<
  |/\/\..../\/\\\
  |||||....||||//
  |||||....||||\\
  .             .
  .             .
  |||||....||||//
  |||||....||||\\
  \/\/\..../\/\//

..and then we must remember to end the output with "LOL".

Run time O(1).
(Except that the input must be read and discarded to get to the next case.)
*/

import java.io.*;
import java.util.*;

public class cellsman_bn {
  static BufferedReader ds = new BufferedReader(new InputStreamReader(System.in));
  static StringTokenizer st;

  static String STR() {
    while (st == null || !st.hasMoreTokens()) st = new StringTokenizer(read());
    return st.nextToken();
  }

  static int INT() {
    return Integer.parseInt(STR());
  }

  static String read() { try {
    return ds.readLine();
  } catch (Exception e) { throw new Error(e); }}

  public static void main(String[] a) {
    int T = INT();
    while (T --> 0) new cellsman_bn().go();
    System.out.println("LOL");
  }

  private void go() {
    int x = INT(), y = INT();
    for (int i = 0; i < y; ++i) STR();  // Skip input lines.
    System.out.println((x > 1 && y > 1) ? (x*y + 1) / 2 * 2 : 2*(x*y - 1));
  }
}
