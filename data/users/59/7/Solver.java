import java.util.Scanner;
import java.util.regex.Pattern;
import java.util.regex.Matcher;
import java.math.BigInteger;

public class Solver {
    public static void main(String[] args) {
        Scanner input = new Scanner(System.in);

        int T = input.nextInt();input.nextLine();
        Pattern p = Pattern.compile("\\s*\\d+\\s*");

        for (int i = 0; i < T; ++i) {
            String line = input.nextLine();

            Matcher m = p.matcher(line);
            if (m.matches()) {
                BigInteger value = new BigInteger(line.replaceAll("\\s", ""));

                System.out.println(value);
            } else {
                System.out.println("invalid input");
            }
        }
    }
}
