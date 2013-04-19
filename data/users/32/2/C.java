import java.util.Scanner;

class C {
    public static void main(String [] a) {
        Scanner s = new Scanner(System.in);

        int cases = s.nextInt();

        for (int i = 0; i < cases; i++) {
            String word = s.next();

            if (word.contains("lol")) {
                System.out.println("0");
            } else if (word.contains("lo") || word.contains("ol") || word.contains("ll")) {
                System.out.println("1");
            } else if (word.matches(".*l.l.*")) {
                System.out.println("1");
            } else if (word.contains("l") || word.contains("o")) {
                System.out.println("2");
            } else {
                System.out.println("3");
            }
        }
    }
}
