import java.util.Scanner;

public class Number {

    public static void main(String[] args) { 
        
        try {
            
            Scanner scan = new Scanner(System.in);
            int n = Integer.parseInt(scan.nextLine());

            for (int i = 0; i < n; i++) {
                String line = scan.nextLine().trim();

                //System.out.println(line);

                if (line.matches("[0-9]+")) {
                    if (line.matches("[0]+")) {
                        System.out.println("0");
                        continue;
                    }
                    while (line.length() != 0 && line.charAt(0) == '0') {
                        line = line.substring(1);
                    }
                    System.out.println(line);
                } else {
                    System.out.println("invalid input");
                }
                
            }

            scan.close();
            
        } catch (Exception e) {
            e.printStackTrace();
        }
        
    }

}
