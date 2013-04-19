import java.util.Scanner;

public class Cross {
	public static void main(String[] args) {
		Scanner inn = new Scanner(System.in);

		int tests = inn.nextInt();

		for (int i = 0; i < tests; i++) {
			int num = inn.nextInt();
			int length = inn.nextInt();

			if (num == 0) {
				System.out.println(0);
				return;
			}

			double [] people = new double[num];

			for (int j = 0; j < num; j++) {
				int speed = inn.nextInt();
				people[j] = ((double)length / (double)speed) + (double)j;
			}

			int laps = 1;

			for (int j = 1; j < num; j++) {
				if (people[j] < people[j-1]) {
					laps++;
				}
			}

			System.out.println(laps);
		}
	}
}
