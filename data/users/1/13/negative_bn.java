/*
Solution to 'Negative people in da house' by Børge Nordli, for IDI Open 2013

Assume there are initially 0 people in the house. Keep a running count of
how many people there are at any time in the house, including negatives.

If this count never goes below 0, the correct answer is 0.
Otherwise, remember the minimum, d, and output -d.

Run time O(M).
*/

import java.io.*;
import java.util.*;

public class negative_bn {
  static BufferedReader ds = new BufferedReader(new InputStreamReader(System.in));
  static PrintStream ut = System.out;
  static StringTokenizer st;

  static String STR() {
    while (st == null || !st.hasMoreTokens()) st = new StringTokenizer(LINE());
    return st.nextToken();
  }

  static int INT() {
    return Integer.parseInt(STR());
  }

  static String LINE() { try {
    return ds.readLine();
  } catch (Exception e) { throw new Error(e); }}


  public static void main(String[] a) {
    int T = INT();
    while (T --> 0) new negative_bn().go();
  }

  private void go() {
    int M = INT();

    int deficit = 0;
    int count = 0;
    for (int i = 0; i < M; ++i) {
      count += INT();
      count -= INT();
      if (count < deficit) {
        deficit = count;
      }
    }

    System.out.println(-deficit);
  }
}
