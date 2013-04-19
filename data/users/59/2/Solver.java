import java.util.Scanner;

public class Solver {
    public static void main(String[] args) {
        Scanner input = new Scanner(System.in);

        int T = input.nextInt();input.nextLine();

        for (int i = 0; i < T; ++i) {
            String line = input.nextLine();

            if (line.matches(".*lol.*")) {
                System.out.println("0");
            } else if (line.matches(".*lo.*") ||
                    line.matches(".*ol.*") ||
                    line.matches(".*ll.*") ||
                    line.matches(".*l[^o]l.*")) {
                System.out.println("1");
            } else if (line.matches(".*l.*") ||
                    line.matches(".*o.*")) {
                System.out.println("2");
            } else {
                System.out.println("3");
            }
        }
    }
}
