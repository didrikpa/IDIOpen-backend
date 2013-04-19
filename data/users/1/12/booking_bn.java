/*
Solution to 'Booking' by Børge Nordli, for IDI Open 2013

Using a standard max flow algorithm, not optimized for sparse graphs.
The max flow is rebuilt each time a request arrives.

Run time O(B*N^3).
*/

import java.io.*;
import java.util.*;

/* "lift-to-front" from a cook book */
class MaxFlow {
  int n;
  public int[] h;
  public int[] current;
  public int[][] c;
  public int[] e;
  public int[][] f;

  public MaxFlow(int n) {
    this.n = n;
    h = new int[n];
    current = new int[n];
    c = new int[n][n];
    e = new int[n];
    f = new int[n][n];
  }

  void start(int s, int t) {
    h[s] = n;
    for (int u = 0; u < n; ++u) {
      f[s][u] = c[s][u];
      f[u][s] = -c[s][u];
      e[u] = c[s][u];
    }
    LinkedList<Integer> L = new LinkedList<Integer>();
    for (int i = 0; i < n; ++i) {
      if (i != s && i != t) {
        L.add(i);
      }
    }

    ListIterator<Integer> li = L.listIterator(0);
    while (li.hasNext()) {
      int u = li.next();
      int old_height = h[u];
      discharge(u);
      if (h[u] > old_height) {
        li.remove();
        L.addFirst(u);
        li = L.listIterator(1);
      }
    }
  }

  void push(int u, int v) {
    int d = Math.min(e[u], c[u][v] - f[u][v]);
    f[u][v] += d;
    f[v][u] = -f[u][v];
    e[u] -= d;
    e[v] += d;
  }

  void lift(int u) {
    for (int i = 0; i < n; ++i) {
      if (c[u][i] > f[u][i] && h[i] < h[u]) {
        h[u] = h[i];
      }
    }
    ++h[u];
  }

  void discharge(int u) {
    while (e[u] > 0) {
      int v = current[u];
      if (v == n) {
        lift(u);
        current[u] = 0;
      } else if (c[u][v] > f[u][v] && h[u] == h[v] + 1) {
        push(u, v);
      } else {
        ++current[u];
      }
    }
  }
}

class Qualification {
  public int id;
  public int count = 1;
  public Qualification(int id) {
    this.id = id;
  }
}

class Employee {
  public List<Qualification> qualifications = new ArrayList<Qualification>();
}

public class booking_bn {
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

  Employee[] employees;
  Map<String, Qualification> qualifications;
  Map<Integer, List<Qualification>> bookings;
  int N;

  private boolean bookingOk(int id) {
    if (bookings.containsKey(id)) {
      // Booking id exists. Ignore the rest of the input.
      int num = INT();
      for (int i = 0; i < num; ++i) {
        STR();
      }
      return false;
    }

    List<Qualification> booking = new ArrayList<Qualification>();
    bookings.put(id, booking);

    boolean ret = bookingOkInternal(id, booking);
    if (!ret){
      bookings.remove(id);
    }

    return ret;
  }

  private boolean bookingOkInternal(int id, List<Qualification> booking) {
    // Parse and create the new booking
    int num = INT();
    for (int i = 0; i < num; ++i) {
      String q = STR();
      booking.add(qualifications.get(q));
    }

    // Create the max-flow matrix with the following nodes:
    // 0: source
    // 1: target
    // 2...: N employees
    // 2 + N...: Q qualifications
    // 2 + N + Q...: B bookings

    int Q = qualifications.size();
    int B = bookings.size();
    int n = 2 + N + Q + B;

    int[][] flow = new int[n][n];
    for (int i = 0; i < n; ++i) {
      flow[i] = new int[n];
    }

    for (int i = 0; i < N; ++i) {
      // Path from the source to each employee
      flow[0][2 + i] = 1;

      // Path from each employee to each qualification
      for (Qualification q : employees[i].qualifications) {
        flow[2 + i][2 + N + q.id] = 1;
      }
    }

    int total = 0;
    {
      int i = 0;
      int[] qualSum = new int[Q];
      for (int bookingId : bookings.keySet()) {
        int sum = 0;
        for (Qualification q : bookings.get(bookingId)) {
          if (q == null) {
            // Qualification is not known, this booking can never be fulfilled.
            return false;
          }

          // Increase capacity from this qualification to the booking
          ++flow[2 + N + q.id][2 + N + Q + i];
          ++qualSum[q.id];
          if (qualSum[q.id] > q.count) {
            // Number of qualifications needed is larger than what is available.
            return false;
          }

          ++sum;
        }

        // Path from each booking to target
        flow[2 + N + Q + i][1] = sum;
        ++i;
        total += sum;
        if (total > N) {
          // More qualifications than employees needed.
          return false;
        }
      }
    }

    MaxFlow maxFlow = new MaxFlow(n);
    maxFlow.c = flow;
    maxFlow.start(0, 1);

    // The booking is accepted if the max flow sums up to all qualifications of all current bookings.
    int sum = 0;
    for (int i = 2; i < 2 + N; ++i) {
      sum += maxFlow.f[0][i];
    }

    return sum == total;
  }

  public static void main(String[] a) {
    int T = INT();
    while (T --> 0) new booking_bn().go();
  }

  private void go() {
    N = INT();
    int C = INT();

    qualifications = new HashMap<String, Qualification>();
    int nextId = 0;

    // Read all employees, create qualifications on the fly
    employees = new Employee[N];
    for (int i = 0; i < N; ++i) {
      employees[i] = new Employee();
      int c = INT();
      for (int j = 0; j < c; ++j) {
        String qualification = STR();
        Qualification q;
        if (qualifications.containsKey(qualification)) {
          q = qualifications.get(qualification);
          ++q.count;
        } else {
          q = new Qualification(nextId++);
          qualifications.put(qualification, q);
        }

        employees[i].qualifications.add(q);
      }
    }

    // Read all bookings
    bookings = new HashMap<Integer, List<Qualification>>();
    for (int i = 0; i < C; ++i) {
      String type = STR();
      int id = INT();
      if (type.equals("cancel")) {
        System.out.println((bookings.containsKey(id) ? "Accep" : "Rejec") + "ted");
        bookings.remove(id);
      } else {
        System.out.println((bookingOk(id) ? "Accep" : "Rejec") + "ted");
      }
    }
  }
}