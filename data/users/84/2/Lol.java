import java.util.Scanner;

public class Lol {
	public static void main(String []args) {
		
		Scanner inn = new Scanner(System.in);

		int counter = inn.nextInt();


		for(int i = 0; i < counter; i++) {
			String s = inn.next();
			if (s.contains("lol")) {
				System.out.println(0);
			} else {
				System.out.println(count(s));
			}
		}
	}

	static int count(String s) {
		char [] chars = s.toCharArray();

		int changes = 3;

		for (int i = 0; i < chars.length - 1; i++) {
			if (chars[i] == 'o') {
				if (chars[i+1] == 'l') {
					return 1;
				} else {
					changes = 2;
				}
			} else if (chars[i] == 'l') {
				if (chars[i+1] == 'o' || chars[i+1] == 'l') {
					return 1;
				} else if (i < chars.length - 2 && chars[i+2] == 'l') {
					return 1;
				} else {
					changes = 2;
				}
			}
		}

		if (chars[chars.length - 1] == 'l' || chars[chars.length - 1] == 'o') {
			return 2;
		}

		return changes;
	}
}
