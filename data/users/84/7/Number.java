import java.util.Scanner;

public class Number {
	public static void main(String args[]) {

		Scanner sc = new Scanner(System.in);
		
		int n = sc.nextInt();
		sc.nextLine();
		
		while(0<n) {
			String s = sc.nextLine();
			
			s = s.trim();

			if(s.equals("")) {System.out.println("invalid input"); n--; continue;}
			if(s.matches("[0]*")) {System.out.println("0"); n--; continue;}
			if(s.matches("[0-9]*")) {
				s = s.replaceFirst("[0]*", "");
				System.out.println(s);
			} else {
				System.out.println("invalid input");
			}
			n--;
		}
	}
}
