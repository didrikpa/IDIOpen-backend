import java.util.Scanner;

public class LOL {

    Scanner sc;

    final String text = "lol";


    public LOL() {

        readFile();
        
    }

    public void readFile() {
        try {

            sc = new Scanner(System.in);

            int cnt = Integer.parseInt(sc.nextLine());

            for (int j = 0; j < cnt; j++) {
                String input = sc.nextLine();

                int magic = 3;

                int len = input.length();


                if (input.contains(text)) {
                    magic = 0;
                } else if (input.contains("ol") || input.contains("lo") || input.contains("ll")) {
                    magic = 1;
                } else {
                    for(int i = 0; i < len; i++) {
                        if (input.charAt(i) == 'l' || input.charAt(i) == 'o') {
                            magic = 2;
                        }
                    }
                }  
                System.out.println(magic);

            }

            sc.close();

        } catch (Exception e) {
            e.printStackTrace();
        }        
    }


    public static void main(String[] args) {
        new LOL();
    }

}
