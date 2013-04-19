/*
Solution to 'dimensions' by Børge Nordli, for IDI Open 2013

Use object oriented programming to make the program clean:
  The main object is a Value consisting of a size (of type double)
  and the dimensions (an array of size 6, containing the power of each of the standard units).
  The following operations are defined:

X + Y: The two values must have exactly the same dimensions.
       The size of the sum is X.size + Y.size.

X - Y: The two values must have exactly the same dimensions.
       The size of the difference is X.size - Y.size.

X * Y: The size of the product is X.size * Y.size.
       The dimensions of the product is the sum of the respective dimensions.

X^N:   The size of the expression is X.size^N.
       Each dimension power is multiplied by N.

When the unit definitions are read, convert them directly to SI units.
Use the last operation to convert unit powers.

Keep a hash map from units to their respective Value, and use string.split to parse the input.
Some cleverness is needed to parse the following values:

"2"         // no units
"2 / unit"  // only negative units
"-2 s"      // negative values
"1E-2"     // negative exponents

Run time O(U + N).
*/

import java.io.*;
import java.util.*;

public class dimensions_bn {
  static BufferedReader ds = new BufferedReader(new InputStreamReader(System.in));
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

  // The names of the SI units.
  private static String[] dimNames = { "m", "kg", "s", "A", "K", "cd" };

  private static int DIM = dimNames.length;

  // The list of known non-standard units.
  private static Map<String, Value> values = new HashMap<String, Value>();

  private static class Value {
    private double size;
    private int[] dim = new int[DIM];

    private static Value NaN = new Value(0, null);

    private Value(double size, int[] dim) {
      this.size = size;
      this.dim = dim;
    }

    // Static parse constructor.
    public static Value parse(String s) {
      s = s.trim();

      // Check for operators.
      if (s.contains(" + ")) {
        String[] parts = s.split(" \\+ ");
        return Value.parse(parts[0]).add(Value.parse(parts[1]));
      } else if (s.contains(" - ")) {
        String[] parts = s.split(" - ");
        return Value.parse(parts[0]).subtract(Value.parse(parts[1]));
      } else if (s.contains(" * ")) {
        String[] parts = s.split(" \\* ");
        return Value.parse(parts[0]).multiply(Value.parse(parts[1]));
      }

      // No operators: Start with 1.
      Value v = new Value(1, new int[DIM]);
      String[] parts = s.trim().split(" ");

      int mult = 1;
      boolean first = true;
      for (String part : parts) {
        if (part.equals("")) {
          // Empty token: Skip it.
        } else if (first && part.charAt(0) != '/' && !Character.isLetter(part.charAt(0))) {
          // The first character is a number: Parse the size.
          v.size = Double.parseDouble(part);
        } else if (part.equals("/")) {
          // Division bar is found. Subsequent units have negative power.
          mult = -1;
        } else {
          // unit^1 is implied
          String dim = part;
          int pow = 1;

          // Check for power mark.
          int powMark = part.indexOf('^');
          if (powMark >= 0) {
            // Power mark is found. Parse the power.
            pow = Integer.parseInt(dim.substring(powMark + 1));
            dim = dim.substring(0, powMark);
          }

          boolean found = false;
          for (int j = 0; j < DIM; ++j) {
            if (dim.equals(dimNames[j])) {
              // Found SI unit. Modify the unit dimension
              v.dim[j] += mult*pow;
              found = true;
            }
          }

          if (!found) {
            // Did not find an SI unit. The unit must be known.
            // Multiply by unit^pow.
            if (!values.containsKey(dim)) {
              System.out.println(dim + " is unknown");
            }
            v = v.multiply(values.get(dim).power(mult*pow));
          }
        }
        first = false;
      }

      return v;
    }

    private static boolean compatible(int[] d1, int[] d2) {
      for (int i = 0; i < DIM; ++i) {
        if (d1[i] != d2[i]) {
          return false;
        }
      }
      return true;
    }

    private static int[] sum(int[] d1, int[] d2) {
      int[] ret = new int[DIM];
      for (int i = 0; i < DIM; ++i) {
        ret[i] = d1[i] + d2[i];
      }
      return ret;
    }

    private static int[] pow(int[] d, int p)
    {
      int[] ret = new int[DIM];
      for (int i = 0; i < DIM; ++i) {
        ret[i] = p*d[i];
      }
      return ret;
    }

    public Value add(Value other) {
      if (!compatible(dim, other.dim)) {
        return NaN;
      }
      // Add the sizes.
      return new Value(size + other.size, dim);
    }

    public Value subtract(Value other) {
      if (!compatible(dim, other.dim)) {
        return NaN;
      }
      // Subtract the sizes.
      return new Value(size - other.size, dim);
    }

    public Value multiply(Value other) {
      return new Value(size * other.size, sum(dim, other.dim));
    }

    public Value power(int p) {
      if (p == 1) {
        // For efficiency.
        return this;
      }
      return new Value(Math.pow(size, p), pow(dim, p));
    }

    public String toString() {
      if (this == NaN) {
        return "Incompatible";
      }

      StringBuilder ret = new StringBuilder();
      ret.append(size);
      // Look for positive powers.
      for (int i = 0; i < DIM; ++i) {
        if (dim[i] > 0) {
          ret.append(" " + dimNames[i]);
          if (dim[i] > 1) {
            // Only write power if > 1.
            ret.append("^" + dim[i]);
          }
        }
      }

      // Look for negative powers.
      boolean first = true;
      for (int i = 0; i < DIM; ++i) {
        if (dim[i] < 0) {
          if (first) {
            ret.append(" /");
            first = false;
          }
          ret.append(" " + dimNames[i]);
          if (dim[i] < -1) {
            // Only write power if < -1.
            ret.append("^" + (-dim[i]));
          }
        }
      }
      return ret.toString();
    }
  }

  public static void main(String[] a) {
    new dimensions_bn().go();
  }

  private void go() {
    values.clear();

    // Read unit definitions.
    int U = INT();
    while (U --> 0) {
      String[] parts = LINE().split("=");
      values.put(parts[0].trim(), Value.parse(parts[1].trim()));
    }

    // Parse expressions.
    int N = INT();
    while (N --> 0) {
      System.out.println(Value.parse(LINE()));
    }
  }
}
