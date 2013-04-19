import java.util.Scanner;
public class Number {
	public static void main(String[] args) {
		Scanner scan = new Scanner(System.in);
		int n = new Integer(scan.nextLine());
		String[] lines = new String[n];
		int[] answers = new int[n];
		int i = 0;
		while(i < n) {
			lines[i] = scan.nextLine().trim();
			if(lines[i].length() != 0)
				answers[i] = isNumber(lines[i]);
			else 
				answers[i] = -1;
			i++;
		}
		
		for(int ans : answers) {
			if(ans == -1) {
				System.out.println("invalid input");
			} else 
				System.out.println(ans);
		}
	}

	public static int isNumber(String line) {
		if(line.contains(" ")) return -1;
		else {
			for(int i=0; i<line.length(); i++) {
				if(!Character.isDigit(line.charAt(i))) {
					return -1;
				}
			} return validNumber(line);
		}	
	}
	public static int validNumber(String number) {
		while (number.length() != 1) {
			if(number.charAt(0) != '0') {
				return new Integer(number);
			}
			number = number.substring(1);
		} return new Integer(number);
	}	
}
