import java.util.Scanner;


public class ProblemH {
	
	public static int t;
	
	public static void main(String[] args) {
		Scanner tast = new Scanner(System.in);
		t = tast.nextInt();
		tast.nextLine();
		while(t-- > 0) {
			String in = tast.nextLine();
			in = in.trim();
			boolean ok = true;
			for(int i = 0; ok && i < in.length(); i++) {
				ok = in.charAt(i) <= '9' && in.charAt(i) >= '0';
			}
			if(!ok || in.isEmpty())
				System.out.println("invalid input");
			else {
				int zero = 0;
				while(zero < in.length() && in.charAt(zero) == '0')
					zero++;
				if(zero == in.length())
					System.out.println(0);
				else
					System.out.println(in.substring(zero));
			}
			
		}
	}

}
