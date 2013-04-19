/*
Solution to 'space' by Børge Nordli, for IDI Open 2013.

First compute distances between all elements of space:
  * From the start point to the end point (simple 3D point-point distance)
  * From the start and end point to all worm tubes (not too hard 3D point-segment distance)
  * From all worm tubes to all other worm tubes (quite difficult 3D segment-segment distance)

All distances can be computed analytically in linear time.

When all distances are computed, run Dijkstra using Java's TreeSet as a priority queue
to find the shortest path from the start node to the end node.

Run time dominated by Dijkstra: O(N^2 + N log N) = O(N^2).
*/

import java.io.*;
import java.util.*;

public class space_bn {
  static BufferedReader ds = new BufferedReader(new InputStreamReader(System.in));
  static StringTokenizer st;

  static String STR() {
    while (st == null || !st.hasMoreTokens()) st = new StringTokenizer(LINE());
    return st.nextToken();
  }

  static int INT() {
    return Integer.parseInt(STR());
  }

  double[] POINT() {
    return new double[] { INT(), INT(), INT() };
  }

  static String LINE() { try {
    return ds.readLine();
  } catch (Exception e) { throw new Error(e); }}

  double sq(double a) {
    return a*a;
  }

  /* Point-point distance squared */
  double distPP2(double[] p1, double[] p2) {
    return sq(p1[0] - p2[0]) +
           sq(p1[1] - p2[1]) +
           sq(p1[2] - p2[2]);

  }

  /* Point-point distance */
  double distPP(double[] p1, double[] p2) {
    return Math.sqrt(distPP2(p1, p2));
  }

  /* Helper function for the segment computations */
  double d(double[] p1, double[] p2, double[] p3, double[] p4) {
    return (p1[0] - p2[0])*(p3[0] - p4[0]) +
           (p1[1] - p2[1])*(p3[1] - p4[1]) +
           (p1[2] - p2[2])*(p3[2] - p4[2]);
  }

  /* Point-segment distance */
  double distPL(double[] p, double[] p1, double[] p2) {
    // Let x = p1 + u(p2 - p1), then we want to find the u that minimizes distPP(p, x).
    double u = d(p, p1, p2, p1) / distPP2(p1, p2);

    // Restrict x to be between p1 and p2 (inclusive).
    double[] x;
    if (u < 0) {
      x = p1;
    } else if (u > 1) {
      x = p2;
    } else {
      x = new double[3];
      x[0] = p1[0] + u * (p2[0] - p1[0]);
      x[1] = p1[1] + u * (p2[1] - p1[1]);
      x[2] = p1[2] + u * (p2[2] - p1[2]);
    }

    return distPP(p, x);
  }

  /* Segment-segment distance */
  double distLL(double[] p1, double[] p2, double[] p3, double[] p4) {
    // Let x = p1 + ua(p2 - p1) and y = p3 + ub(p4 - p3)
    // We want to find the pair (ua, ub) that minimizes distPP(x, y)
    // Formulas taken from a cook book.
    double top =    d(p1, p3, p4, p3) * d(p4, p3, p2, p1) - d(p1, p3, p2, p1) * d(p4, p3, p4, p3);
    double bottom = d(p2, p1, p2, p1) * d(p4, p3, p4, p3) - d(p4, p3, p2, p1) * d(p4, p3, p2, p1);

    if (Math.abs(bottom) < 1E-9) {
      // Lines are parallell, a shortest distance is guaranteed to be found
      // if we restrict x = p1, x = p2, y = p3 or y = p4.
      // Fall back to point-segment distance.
      return Math.min(Math.min(distPL(p1, p3, p4), distPL(p2, p3, p4)),
                      Math.min(distPL(p3, p1, p2), distPL(p4, p1, p2)));
    }

    double ua = top / bottom;
    double ub = (d(p1, p3, p4, p3) + ua * d(p4, p3, p2, p1)) / d(p4, p3, p4, p3);

    // Restrict x and y to lie inside the segments.
    if (ua < 0) {
      if (ub < 0) {
        // Take minimum of solutions restricted to ua = 0 and ub = 0.
        return Math.min(distPL(p1, p3, p4), distPL(p3, p1, p2));
      } else if (ub > 1) {
        // Take minimum of solutions restricted to ua = 0 and ub = 1.
        return Math.min(distPL(p1, p3, p4), distPL(p4, p1, p2));
      }
      // Restrict ua = 0.
      return distPL(p1, p3, p4);
    } else if (ua > 1) {
      if (ub < 0) {
        // Take minimum of solutions restricted to ua = 1 and ub = 0.
        return Math.min(distPL(p2, p3, p4), distPL(p3, p1, p2));
      } else if (ub > 1) {
        // Take minimum of solutions restricted to ua = 1 and ub = 1.
        return Math.min(distPL(p2, p3, p4), distPL(p4, p1, p2));
      }
      // Restrict ua = 1.
      return distPL(p2, p3, p4);
    } else if (ub < 0) {
      // Restrict ub = 0.
      return distPL(p3, p1, p2);
    } else if (ub > 1) {
      // Restrict ub = 1.
      return distPL(p4, p1, p2);
    }

    double[] x = new double[3];
    x[0] = p1[0] + ua * (p2[0] - p1[0]);
    x[1] = p1[1] + ua * (p2[1] - p1[1]);
    x[2] = p1[2] + ua * (p2[2] - p1[2]);

    double[] y = new double[3];
    y[0] = p3[0] + ub * (p4[0] - p3[0]);
    y[1] = p3[1] + ub * (p4[1] - p3[1]);
    y[2] = p3[2] + ub * (p4[2] - p3[2]);

    return distPP(x, y);
  }

  static int K;
  static double[][] dists;
  static Node[] nodes;

  TreeSet<Node> queue = new TreeSet<Node>();

  void dijkstra() {
    // Remove nodes from the priority queue and process them until
    // we have reached the target node.
    while (!queue.isEmpty()) {
      Node node = queue.first();
      queue.remove(node);
      if (node.target) {
        return;
      }
      node.search();
    }
  }

  int count = 0;
  class Node implements Comparable<Node> {
    int n = count++;

    double distance = Double.MAX_VALUE;
    boolean target = false;
    boolean done = false;
    boolean visited = false;

    void search() {
      done = true;
      for (int i = 0; i < K; ++i) {
        nodes[i].visit(distance + dists[n][i]);
      }
    }

    // Visit this node with the given distance.
    void visit(double dist) {
      if (done) {
        // Nothing to do.
      } else if (!visited) {
        // Insert this node in the queue.
        visited = true;
        distance = dist;
        queue.add(this);
      } else if (dist < distance) {
        // Update the priority queue with the new and lower distance.
        queue.remove(this);
        distance = dist;
        queue.add(this);
      }
    }

    // To be used to order the nodes in the priority queue.
    public int compareTo(Node o) {
      if (distance < o.distance) {
        return -1;
      } else if (distance > o.distance) {
        return 1;
      }
      return n - o.n;
    }
  }

  public static void main(String[] a) {
    int T = INT();
    while (T --> 0) new space_bn().go();
  }

  private void go() {
    int N = INT();
    K = N + 2;
    double[] start = POINT();
    double[] end = POINT();

    double[][] lineStart = new double[N][];
    double[][] lineEnd = new double[N][];
    for (int i = 0; i < N; ++i) {
      lineStart[i] = POINT();
      lineEnd[i] = POINT();
    }

    dists = new double[N+2][];
    for (int i = 0; i < N + 2; ++i) {
      dists[i] = new double[N+2];
    }

    // From start point to end point.
    dists[0][1] = dists[1][0] =
      distPP(start, end);

    // From start and end point to all worm tubes.
    for (int i = 0; i < N; ++i) {
      dists[0][i+2] = dists[i+2][0] =
        distPL(start, lineStart[i], lineEnd[i]);
      dists[1][i+2] = dists[i+2][1] =
        distPL(end, lineStart[i], lineEnd[i]);
    }

    // From all worm tubes to all other worm tubes.
    for (int i = 0; i < N; ++i) {
      for (int j = i + 1; j < N; ++j) {
        dists[i+2][j+2] = dists[j+2][i+2] =
          distLL(lineStart[i], lineEnd[i],
                 lineStart[j], lineEnd[j]);
      }
    }
    /*
    for (int i = 0; i < K; ++i) {
      for (int j = 0; j < K; ++j) {
        System.out.print(" " + dists[i][j]);
      }
      System.out.println();
    }
    */

    nodes = new Node[K];
    for (int i = 0; i < K; ++i) {
      nodes[i] = new Node();
      if (i == 0) {
        nodes[i].distance = 0;
      } else if (i == 1) {
        nodes[1].target = true;
      }

    }

    queue.add(nodes[0]);
    dijkstra();
    System.out.println(nodes[1].distance);
  }
}
