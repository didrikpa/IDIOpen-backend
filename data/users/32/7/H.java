import java.util.Scanner;
import java.math.BigInteger;

class H {
    public static void main(String [] args) {
        Scanner s = new Scanner(System.in);
        int cases = Integer.parseInt(s.nextLine().trim());

        next: 
        for (int i = 0; i < cases; i++) {
            String line = s.nextLine().trim();

            if (line.length() == 0) {
                System.out.println("invalid input");
                continue;
            }

            for (char c : line.toCharArray()) {
                if (c < '0' || c > '9') {
                    System.out.println("invalid input");
                    continue next;
                }
            }

            line = line.replaceFirst("^0*", "");
            if (line.length() == 0) {
                System.out.println("0");
            } else {
                System.out.println(line);
            }
        }
    }
}
