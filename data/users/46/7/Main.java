import java.util.Scanner;


public class Main {

	public static void main(String[] args){
		Scanner in = new Scanner(System.in);
		int testCases = in.nextInt();
		in.nextLine();
		for(int i = 0; i < testCases; i++){
			int start = 0;
			String input = in.nextLine();
			int end = input.length()-1;
			boolean isInteger = false;
			for(int j = 0; j < input.length(); j++){
				if(!(input.charAt(j) == ' ')){
					if(input.charAt(j) >= '0' && input.charAt(j) <= '9'){
						start = j;
						isInteger = true;
						break;
					}
					else{
						isInteger = false;
						break;
					}
				}
			}
			if(isInteger){
				for(int j = start+1; j < input.length(); j++){
					if(!(input.charAt(j) >= '0' && input.charAt(j) <= '9')){
						if(input.charAt(j) == ' '){
							end = j-1;
							break;
						}
						else{
							isInteger = false;
							break;
						}
					}
				}

			}
			if(isInteger){
				for(int j = end+1; j < input.length(); j++){
					if(!(input.charAt(j) == ' ')){
						isInteger = false;
						break;
					}
				}
			}
			if(isInteger){
				for(int j = start; j<=end; j++){
					start = j;
					if(!(input.charAt(j) == '0')){
						break;
					}

				}
				System.out.println(input.substring(start, end+1));
			}
			else{
				System.out.println("invalid input");
			}
		}
	}
}