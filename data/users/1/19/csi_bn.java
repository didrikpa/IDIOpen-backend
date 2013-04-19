/*
Solution to 'C.S.I: P15' by Børge Nordli, for IDI Open 2013

Use simple depth-first flood fill of the input in two stages:
1) To find the flowers, check all pixels just above the ground:
   If the pixel is not air, increase the number of flowers by 1 and
   clear all connected pixels.

2) To find the birds, check all remaining pixels.
   If the pixels have a /\/\ pattern, and the size of the connected
   group is exactly 4, increase the number of birds by 1.
   In any case, clear all connected pixels.

A standard trick in DFS is to add empty sentinels around the border of
the data. Just make sure to 1-index all your data accesses.

Run time O(W*H).
*/

import java.io.*;
import java.util.*;

public class csi_bn {
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

  static double DBL() {
    return Double.parseDouble(STR());
  }

  static String LINE() { try {
    return ds.readLine();
  } catch (Exception e) { throw new Error(e); }}


  public static void main(String[] a) {
    int T = INT();
    while (T --> 0) new csi_bn().go();
  }

  int[][] data;

  private int DFS(int i, int j)
  {
    if (data[i][j] == 0) return 0;
    data[i][j] = 0;

    // Do an 8-connected depth first search while counting the size of the group.
    // It should be O.K. to do this on the stack, since the input sizes are fairly small.
    return 1 + DFS(i-1, j-1) + DFS(i-1, j) + DFS(i-1, j+1) +
               DFS(i,   j-1) +               DFS(i,   j+1) +
               DFS(i+1, j-1) + DFS(i+1, j) + DFS(i+1, j+1);
  }

  private void go() {
    int H = INT();
    int W = INT();

    data = new int[H + 1][W + 2];
    for (int i = 1; i < H; ++i) {
      String line = STR();
      for (int j = 0; j < W; ++j) {
        switch (line.charAt(j)) {
          case '/':
            data[i][j+1] = 1;
            break;
          case '\\':
            data[i][j+1] = 2;
            break;
          // No need to differentiate the rest of the values.
          case '|':
          case '@':
          case '-':
            data[i][j+1] = 3;
        }
      }
    }

    // Skip the ground line and use it as sentinel instead.
    STR();

    // Search for flowers using simple DFS from the ground.
    int flowers = 0;
    for (int i = 1; i <= W; ++i) {
      if (data[H-1][i] > 0) {
        ++flowers;
        DFS(H-1, i);
      }
    }

    // Search for birds matching the bird pattern and finding the size of the group
    int birds = 0;
    for (int i = 1; i <= H; ++i) {
      for (int j = 1; j+3 <= W; ++j) {
        if (data[i][j] == 1 && data[i][j+1] == 2 &&
            data[i][j+2] == 1 && data[i][j+3] == 2) {
          if (DFS(i, j) == 4) {
            ++birds;
          }
        } else {
          DFS(i, j);
        }
      }
    }

    System.out.println("Flowers: " + flowers);
    System.out.println("Birds: " + birds);
  }
}
