import java.util.Scanner;


public class LOL {
	
	public static void main(String[] args) {
		Scanner scan = new Scanner(System.in);
		int n = scan.nextInt();
		String[] words = new String[n];
		int[] answers = new int[n];
		for(int i=0; i<n; ++i) {
			words[i] = scan.next();
			answers[i] = minChange(words[i]);
		}
		for(int ans : answers) {
			System.out.println(ans);
		}
	}
	
	public static int minChange(String word) {
		if(word.contains("lol")) {
			return 0;
		} if(word.contains("lo")) {
			return 1;
		} if(word.contains("ll")) {
			return 1;
		} if(word.contains("ol")) {
			return 1;
		} if(word.contains("l")) {
			for(int i=0; i<word.length(); i++) {
				if(word.charAt(i) == 'l') {
					if(i+2 < word.length()) {
						if(word.charAt(i+2) == 'l') {
							return 1;
						}
					}
				}
			}
			return 2;
		} if(word.contains("o")) {
			return 2;
		} else {
			return 3;
		}
	}
}
