/*
Solution to 'angry' by Børge Nordli, for IDI Open 2013

Tokenize the sentence and check all conditions one by one.
*/

import java.io.*;
import java.util.*;

public class angry_bn {
  static BufferedReader ds = new BufferedReader(new InputStreamReader(System.in));
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
    while (T --> 0) new angry_bn().go();
  }

  private void go() {
    StringTokenizer l = new StringTokenizer(LINE());
    int count = 0;
    boolean watchOutForOf = false;

    while (l.hasMoreTokens()) {
      String s = l.nextToken();
      if ((watchOutForOf && s.equals("of")) ||
          s.contains("lol") ||
          s.equals("u") ||
          s.equals("ur"))
      {
        count += 10;
      }

      watchOutForOf = s.equals("would") || s.equals("should");
    }

    System.out.println(count);
  }
}
