/*
Solution to 'Ruben Spawns' by Børge Nordli, for IDI Open 2013

Order the minions by decreasing work load, and count how many
minions you need before the sum or their work is larger than
or equal to the required work.

Run time O(M * log(M)).
*/

import java.io.*;
import java.util.*;

public class spawns_bn {
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
    while (T --> 0) new spawns_bn().go();
  }

  private void go() {
    int W = INT(), M = INT();
    int[] k = new int[M];
    for (int i = 0; i < M; ++i) {
      k[i] = INT();
    }

    Arrays.sort(k);
    int sum = 0;
    for (int i = M; i --> 0;)
    {
      sum += k[i];
      if (sum >= W) {
        System.out.println(M - i);
        return;
      }
    }

    System.out.println("no rest for Ruben");
  }
}
